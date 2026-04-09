# Plan: eMobility Transparent Laden – Gesamtplanung

Vollständige Plattform für transparenten eMobility Ladedienst (EMSP). Nutzt fremde Ladesäulen via Roaming, verkauft Ladevorgänge an Endkunden mit maximaler Preistransparenz. CI4-Backend, Flutter-App, MariaDB, Jenkins CI/CD. Adapter-Pattern für Provider, immutable Pricing Snapshots, Lexware-Integration.

---

## 1. SYSTEMARCHITEKTUR

```
┌─────────────┐     HTTPS/JWT      ┌──────────────────┐       ┌──────────────┐
│ Flutter App  │◄──────────────────►│  CI4 REST API    │──────►│  FCM (Push)  │
│ (Android/iOS)│◄───── Push ────────│  (Backend)       │       │  via HTTP v1 │
└─────────────┘                    └────────┬─────────┘       └──────────────┘
                                            │
                    ┌───────────────────────┼───────────────────────┐
                    │                       │                       │
              ┌─────▼─────┐          ┌──────▼──────┐        ┌──────▼──────┐
              │ MariaDB   │          │ Roaming     │        │ Lexware API │
              │           │          │ Provider(s) │        │ (Billing)   │
              └───────────┘          │ via Adapter │        └─────────────┘
                                     └─────────────┘
```

### Sync vs. Async

| Prozess | Modus | Grund |
|---------|-------|-------|
| Preisberechnung | Synchron | User wartet auf Preis |
| Session Start/Stop | Synchron | User wartet auf Bestätigung |
| Session-Status-Polling | Synchron (Polling) | Echtzeit-Feedback nötig |
| Charge-Point-Sync von Providern | Async (Cron, alle 10 Min) | Hintergrundprozess |
| Invoice-Erstellung (Lexware) | Async (Cron/Queue) | Darf fehlschlagen + Retry |
| PDF-Download von Lexware | Async (nach Invoice) | Nachgelagert |
| Push-Nachrichten-Versand | Async (Event-driven) | Darf nicht Session-Flow blockieren |
| Live-Kosten-Berechnung | Synchron (auf Polling-Request) | User erwartet aktuelle Daten |
| Session-Event-Persistierung | Synchron (im Status-Update) | Muss vor Push-Dispatch geschrieben sein |

### Zentrale Services

- **AuthService**: JWT-Token-Management, Login, Registration
- **UserService**: Profilverwaltung, Payment-Method-Verwaltung
- **SubscriptionService**: Abo-Lifecycle (erstellen, verlängern, kündigen)
- **ProviderService**: Adapter-Factory, Charge-Point-Sync, Session-Relay
- **PricingEngine**: Preisberechnung, Snapshot-Erstellung, Transparenz-Kalkulation
- **TariffResolver**: StructuredTariff → ResolvedTariff Auflösung (zeitabhängig, gestaffelt)
- **ChargingService**: Session-Lifecycle (Start, Stop, Status)
- **BillingService**: Invoice-Erstellung, Lexware-Sync, PDF-Handling
- **AdminService**: Dashboard-Daten, Nutzerverwaltung, Provider-Management
- **NotificationService**: Event-Dispatch, Push-Versand via FCM, Präferenzprüfung, Token-Management
- **LiveCostService**: Live-Kostenschätzung aus Snapshot + aktuellem Session-Status, Foreground-Notification-Payload

---

## 2. DOMÄNENMODELL / BOUNDED CONTEXTS

| Context | Verantwortung | Abhängigkeiten | Datenfluss |
|---------|---------------|----------------|------------|
| **Auth** | Registration, Login, JWT, Passwort-Reset, E-Mail-Verifikation | Keine (Root-Context) | Erstellt User → gibt JWT aus → validiert Tokens bei jedem Request |
| **Users** | Profile, Zahlungsarten, Präferenzen | Auth (User-Identität) | Profildaten für Billing, Zahlungsart für Pricing |
| **Subscriptions** | Abo-Pläne, User-Abos, Lifecycle | Users, Billing | Abo-Status → Pricing (Rabatt), Billing (Abo-Rechnung) |
| **Providers** | Adapter-Verwaltung, Charge-Point-Sync, Tarif-Import | Keine internen (externe Provider-APIs) | Liefert Charge-Points + Tarife → Pricing, Sessions |
| **Charging** | Session-Lifecycle, Status-Tracking | Providers, Pricing, Users | Session-Start → Pricing-Snapshot, Session-Ende → Billing |
| **Pricing** | Preiskalkulation, Tarif-Normalisierung, Snapshot, Transparenz | Providers, Subscriptions, Users | Berechnet Preis → immutabler Snapshot → Charging + Billing |
| **Billing** | Invoice-Erstellung, Lexware-Integration, PDF-Speicherung | Charging, Pricing, Users | Session-Ende → Invoice lokal → Lexware → PDF |
| **Admin** | System-Config, User-Verwaltung, Provider-Mgmt, Dashboard | Alle (read-only Zugriff) | Liest aus allen Domänen, schreibt in Provider- und System-Config |
| **Notifications** | Push-Versand, Geräte-Management, Nutzerpräferenzen, Event-Dispatch | Charging, Users, Pricing | Session-Events → Präferenzprüfung → Push an registrierte Geräte |

**Kritischer Datenfluss:** Charging-Start → Pricing-Snapshot erstellen (immutabel) → Session aktiv → Session-Ende → Billing (Snapshot-Raten × Verbrauch) → Lexware → PDF

---

## 3. DATENMODELL (Konzeptuell)

### users
- id (PK), email (unique), password_hash, first_name, last_name, phone, street, city, postal_code, country, email_verified_at, status [active|blocked|pending], created_at, updated_at, deleted_at (Soft Delete)
- **Sensibel**: email, password_hash, Adressdaten → DSGVO-relevant
- 1:N → user_subscriptions, payment_methods, charging_sessions, invoices

### user_refresh_tokens
- id (PK), user_id (FK→users), token_hash, device_info, expires_at, created_at
- **Sensibel**: token_hash → gehashed speichern

### subscription_plans
- id (PK), name, slug (unique), description, price_monthly_cent, price_yearly_cent, platform_fee_reduction_percent, features_json, is_active, sort_order, created_at, updated_at
- Muss **versioniert** werden: Alte Abos behalten ihren ursprünglichen Plan

### subscription_plan_versions
- id (PK), plan_id (FK), version, price_monthly_cent, price_yearly_cent, platform_fee_reduction_percent, features_json, valid_from, valid_until, created_at
- **Kritisch**: Preisänderungen dürfen laufende Abos nicht rückwirkend ändern

### user_subscriptions
- id (PK), user_id (FK→users), plan_version_id (FK→subscription_plan_versions), status [active|cancelled|expired|past_due], billing_cycle [monthly|yearly], starts_at, current_period_end, cancelled_at, created_at, updated_at

### payment_methods
- id (PK), user_id (FK→users), type [credit_card|paypal|sepa|apple_pay|google_pay], label, is_default, external_reference (encrypted), fee_model_id (FK), status [active|disabled], created_at, updated_at
- **Sensibel**: external_reference → verschlüsselt speichern

### payment_fee_models
- id (PK), name, payment_type, fixed_fee_cent, percentage_fee (Dezimal z.B. 0.015 = 1.5%), min_fee_cent, max_fee_cent, is_active, valid_from, valid_until, created_at
- **Versioniert**: Fee-Änderungen dürfen laufende Sessions nicht betreffen

### providers
- id (PK), name, slug (unique), adapter_class, config_encrypted (JSON, verschlüsselt), roaming_fee_type [fixed|percentage], roaming_fee_value, is_active, created_at, updated_at
- **Sensibel**: config_encrypted enthält API-Keys/Credentials

### charge_points
- id (PK), provider_id (FK→providers), external_id, name, address, city, postal_code, country, latitude (DECIMAL 10,7), longitude (DECIMAL 10,7), operator_name, is_available, last_synced_at, created_at, updated_at
- **Index**: Spatial Index auf lat/lng für Geo-Queries

### connectors
- id (PK), charge_point_id (FK→charge_points), external_id, connector_type [Type2|CCS|CHAdeMO|Schuko], power_kw (DECIMAL), status [available|occupied|charging|offline|unknown], structured_tariff_json (StructuredTariff als JSON), last_status_update, created_at, updated_at
- **structured_tariff_json**: Enthält alle Tarif-Elemente (Zeitabhängigkeit, Staffelung, Blockiergebühren) exakt wie vom Provider geliefert, nicht vereinfacht

### charging_sessions
- id (PK), user_id (FK→users), connector_id (FK→connectors), provider_id (FK→providers), payment_method_id (FK→payment_methods), pricing_snapshot_id (FK→pricing_snapshots), external_session_id, status [pending|active|completed|failed|cancelled], started_at, ended_at, energy_kwh (DECIMAL 10,4), duration_seconds, blocking_duration_seconds (INT, nullable – Zeit nach Ladeschluss bis Kabelabzug), total_price_cent, currency [EUR], created_at, updated_at
- **blocking_duration_seconds**: Wird vom Provider gemeldet oder berechnet (ended_at bis cable_disconnected_at). Relevant für Blockiergebühren-Berechnung im Final Calculation

### pricing_snapshots ⚡ IMMUTABLE nach Erstellung
- id (PK), session_id (FK→charging_sessions, nullable)
- connector_id (FK), provider_id (FK), user_subscription_id (FK, nullable), payment_method_id (FK)
- calculation_timestamp
- **Tarif-Typ**: tariff_type [simple|time_based|tiered] – Komplexität des Quelltarifs
- **Resolved Tarif (zum Zeitpunkt aufgelöst)**:
  - resolved_per_kwh_tenth_cent, resolved_per_min_tenth_cent, resolved_start_fee_cent, resolved_blocking_fee_per_min_cent
  - resolved_tariff_json (vollständiger ResolvedTariff als JSON für Nachvollziehbarkeit)
- **Structured Tarif (Original-Rohdaten)**: structured_tariff_json (StructuredTariff vom Provider, exakte Kopie zum Snapshot-Zeitpunkt)
- **Roaming-Komponente**: roaming_fee_type, roaming_fee_value, roaming_fee_estimated_cent
- **Plattform-Komponente**: platform_fee_per_kwh_tenth_cent, platform_fee_base_percent, subscription_discount_percent, effective_platform_fee_percent, platform_fee_estimated_cent
- **Payment-Komponente**: payment_fee_fixed_cent, payment_fee_percentage, payment_fee_min_cent, payment_fee_max_cent
  - **Berechnung**: Payment-Fee wird ZULETZT auf den Gesamtbetrag (CPO + Roaming + Plattform) angewendet, nicht als Zwischenschritt
- **Transparenz**: transparency_json (strukturierte Prozentanteile aller Komponenten)
- **Geschätzte Gesamtwerte**: estimated_total_per_kwh_cent, estimated_total_30min_cent, estimated_total_60min_cent
- tariff_version, created_at
- **Kritisch**: Darf NIEMALS nach Erstellung geändert werden
- **Neu**: Enthält sowohl den aufgelösten Tarif (für aktuelle Berechnung) als auch den Original-StructuredTariff (für Audit/Nachvollziehbarkeit)

### invoices
- id (PK), user_id (FK→users), session_id (FK→charging_sessions, nullable – auch für Abo-Rechnungen), invoice_number (unique), invoice_type [charging|subscription], amount_net_cent, tax_percent, tax_amount_cent, amount_gross_cent, currency, line_items_json, lexware_invoice_id, lexware_status [pending|created|sent|paid|overdue|failed], pdf_path, retry_count, last_retry_at, created_at, synced_at
- **line_items_json**: Strukturierte Rechnungspositionen, abgeleitet aus Snapshot + Actuals:
  ```json
  [
    { "pos": 1, "desc": "Ladeenergie 12,4 kWh × 28,6 ct", "net_cent": 355, "tax_pct": 19 },
    { "pos": 2, "desc": "Startgebühr", "net_cent": 100, "tax_pct": 19 },
    { "pos": 3, "desc": "Roaming-Gebühr", "net_cent": 42, "tax_pct": 19 },
    { "pos": 4, "desc": "Plattformgebühr", "net_cent": 84, "tax_pct": 19 },
    { "pos": 5, "desc": "Zahlungsartgebühr (Visa)", "net_cent": 16, "tax_pct": 19 }
  ]
  ```
- **Verwendung**: BillingService generiert line_items aus Snapshot + Session-Actuals. LexwareAdapter mappt line_items auf Lexware-Rechnungspositionen.

### user_devices
- id (PK), user_id (FK→users), platform [android|ios], push_token (unique), device_name (nullable), app_version, notifications_enabled (BOOL, default true), is_active (BOOL, default true), last_seen_at, created_at, updated_at
- **Push-Token**: FCM-Token, pro Gerät eindeutig. Wird bei App-Start und Token-Refresh aktualisiert.
- **Multi-Device**: Ein User kann N Geräte haben. Push geht an ALLE aktiven Geräte mit notifications_enabled=true.
- **Token-Invalidierung**: Wenn FCM HTTP v1 einen `UNREGISTERED`- oder `INVALID_ARGUMENT`-Fehler zurückgibt → is_active=false setzen.
- **Cleanup**: Cron löscht Geräte mit last_seen_at > 90 Tage.
- **Index**: (user_id, is_active) für schnellen Lookup aktiver Geräte eines Users.

### notification_preferences
- id (PK), user_id (FK→users), event_type (VARCHAR, z.B. `session_started`, `blocking_fee_warning`), enabled (BOOL, default true), created_at, updated_at
- **Unique Constraint**: (user_id, event_type)
- **Default-Verhalten**: Wenn kein Record für einen event_type existiert → enabled=true (Opt-out-Modell)
- **Event-Types**:
  - `session_started` – Ladevorgang gestartet
  - `session_active` – Energiefluss bestätigt
  - `session_paused` – Ladevorgang pausiert
  - `session_completed` – Ladevorgang beendet (mit Endkosten)
  - `session_failed` – Fehler an Station
  - `session_live_cost` – Periodische Live-Kosten-Updates
  - `blocking_fee_warning` – Blockiergebühr startet bald (5 Min vorher)
  - `blocking_fee_active` – Blockiergebühr läuft
  - `station_available` – Favorisierte Station wieder verfügbar
  - `tariff_change_hint` – Tarifänderung während Session
  - `invoice_ready` – Rechnung verfügbar

### session_events
- id (PK), session_id (FK→charging_sessions), event_type (VARCHAR), event_data_json (nullable), energy_kwh_at_event (DECIMAL 10,4, nullable), live_cost_cent (INT, nullable), created_at
- **Zweck**: Persistiertes Protokoll aller relevanten Zustandsänderungen einer Session.
- **event_type-Werte**: `started`, `active`, `energy_flowing`, `paused`, `resumed`, `stop_requested`, `completed`, `failed`, `cancelled`, `blocking_started`, `live_cost_update`
- **event_data_json**: Kontextdaten je Typ, z.B. `{"kwh": 5.2, "duration_sec": 600, "provider_status": "Charging"}` oder `{"error_code": "EVSE_FAULT"}`
- **Nicht jedes Event erzeugt Push**: Nur Events, die gemapped sind (siehe Notification-System Sektion) UND Nutzerpräferenz enabled.
- **Write-only**: Wie audit_log – nur Inserts, keine Updates/Deletes.
- **Index**: (session_id, created_at) für chronologisches Lesen.

### admin_users (separate Tabelle, keine Vermischung mit Endkunden)
- id (PK), email (unique), password_hash, display_name, role [super_admin|admin|viewer], status [invited|totp_pending|active|blocked], last_login_at, created_at, updated_at
- **TOTP (Pflicht)**: totp_secret_encrypted, totp_verified_at, recovery_codes_encrypted (JSON-Array, gehashed)
- **TOTP-Status-Ableitung**: Kein separates `totp_status`-Feld. Status wird über `status` abgebildet: `totp_pending` = TOTP noch nicht eingerichtet, `active` = TOTP verifiziert und einsatzfähig.
- **Einladungsflow**: invited_by (FK→admin_users, nullable), invitation_token_hash, invitation_expires_at
- **Status-Machine**: invited (Einladungslink gesendet) → totp_pending (Passwort gesetzt, TOTP noch nicht eingerichtet) → active (TOTP verifiziert, voll einsatzfähig) → blocked (manuell gesperrt)
- **Kritisch**: Kein Admin kann ohne aktives TOTP auf geschützte Endpunkte zugreifen. TOTP-Setup ist erzwungener Schritt nach erstem Login.

### audit_log (Write-only: nur Inserts, niemals Updates oder Deletes)
- id (PK), entity_type, entity_id, action [create|update|delete|login|session_start|...], actor_type [user|admin|system], actor_id, changes_json (old/new), ip_address, created_at

### system_config
- id (PK), config_key (unique), config_value, description, updated_by, updated_at

### Versionierungsstrategie
- **Pricing Snapshots**: Vollständige Kopie aller Preiskomponenten → immutable
- **Subscription Plans**: Versionstabelle, User-Abo zeigt auf spezifische Version
- **Payment Fee Models**: valid_from/valid_until für zeitliche Gültigkeit
- **Provider Config**: Audit-Log trackt Änderungen

---

## 4. PROVIDER-ARCHITEKTUR

### ProviderAdapterInterface

```
- getName(): string
- getCapabilities(): ProviderCapabilities
- syncChargePoints(): SyncResult
- getConnectorStatus(externalConnectorId): ConnectorStatus
- getTariff(externalConnectorId): StructuredTariff
- startSession(StartSessionRequest): ProviderSessionResponse
- stopSession(externalSessionId): ProviderSessionResponse
- getSessionStatus(externalSessionId): SessionStatusResponse
```

### StructuredTariff (Value Object – Rohdaten vom Provider)

Bildet den vollständigen Tarif ab, wie er vom Provider kommt – inklusive Zeitabhängigkeiten und Staffelungen.

```
StructuredTariff:
  elements: TariffElement[]      # Mehrere Elemente möglich (zeitabhängig)
  currency: string (EUR)
  validUntil: ?DateTime

TariffElement:
  restrictions: TariffRestrictions  # Wann gilt dieses Element
  priceComponents: PriceComponent[] # Was kostet es

TariffRestrictions:
  startTime: ?string (HH:MM)       # z.B. "08:00"
  endTime: ?string (HH:MM)         # z.B. "20:00"
  dayOfWeek: ?int[]                 # 1=Mo..7=So, null=alle
  minKwh: ?float                    # Staffelung: ab X kWh
  maxKwh: ?float                    # Staffelung: bis X kWh
  minDurationMinutes: ?int          # Ab X Minuten Ladedauer

PriceComponent:
  type: [ENERGY|TIME|FLAT|PARKING_TIME]  # OCPI-kompatibel
  price: int (Zehntel-Cent)               # Preis pro Einheit (einheitlich Zehntel-Cent, auch FLAT)
  stepSize: int                           # Schrittgröße (z.B. 1 kWh, 1 Min)
```

### ResolvedTariff (Value Object – zum Zeitpunkt aufgelöst)

Ergebnis der Auflösung eines StructuredTariff zum aktuellen Zeitpunkt. Einfach weiterverarbeitbar.

```
ResolvedTariff:
  perKwhTenthCent: int              # Aufgelöster kWh-Preis (Zehntel-Cent)
  perMinuteTenthCent: int           # Aufgelöster Minutenpreis (Zehntel-Cent)
  startFeeCent: int                 # Startgebühr (Cent)
  blockingFeePerMinuteCent: int     # Blockiergebühr nach Ladeende (Cent)
  currency: string (EUR)
  resolvedAt: DateTime              # Zeitpunkt der Auflösung
  appliedElementIndex: int          # Index des genutzten TariffElement
  isTimeDependent: bool             # Hinweis: Tarif ist zeitabhängig
  validUntil: ?DateTime             # Wie lange diese Auflösung gültig ist
```

### TariffResolver (Service)

Löst einen StructuredTariff zu einem ResolvedTariff auf.

```
TariffResolver:
  resolve(StructuredTariff, DateTime now): ResolvedTariff
    1. Iteriert über elements[]
    2. Prüft restrictions gegen aktuellen Zeitpunkt (Uhrzeit, Wochentag)
    3. Wählt passendes Element (First-Match oder Most-Specific)
    4. Extrahiert PriceComponents → mapped auf ResolvedTariff-Felder
    5. Setzt validUntil auf nächste Tarifgrenzzeit (z.B. 20:00 wenn Nachttarif ab 20:00)
    6. Bei keinem Match → Fallback auf Element ohne Restrictions oder Exception
```

**Datenfluss**: Provider.getTariff() → StructuredTariff → TariffResolver.resolve() → ResolvedTariff → PricingEngine

### Adapter-Implementierungen

**MockProvider**
- Gibt fest definierte Charge-Points zurück (konfigurierbar via JSON-Datei)
- Simuliert Session-Lifecycle (Start → Active → Complete)
- Liefert definierte Tarife
- Für lokale Entwicklung und Tests

**OcpiProvider** (OCPI 2.2.1)
- Locations-Modul für Charge-Point-Sync
- Sessions-Modul für Session-Management
- Tariffs-Modul für Tarif-Import
- CDRs-Modul für Charge Detail Records (Abrechnung)

### Erweiterung neuer Provider
1. Neue Adapter-Klasse erstellen, die ProviderAdapterInterface implementiert
2. In `providers`-Tabelle registrieren mit `adapter_class` = FQCN
3. Config (API-Key, Endpunkte) verschlüsselt in `config_encrypted` speichern
4. ProviderFactory instanziiert automatisch den richtigen Adapter

### ProviderFactory
- Liest Provider-Config aus DB
- Instanziiert Adapter anhand von `adapter_class`
- Injiziert entschlüsselte Config
- Cached Adapter-Instanzen pro Request

### Sync-Strategie
- CI4 Command: `php spark provider:sync {provider_slug}`
- Cron: Alle 10 Minuten für aktive Provider
- Differentiell: Nur geänderte Charge-Points/Connectors updaten (Last-Modified, Hash-Vergleich)
- Status-Sync: Connector-Status häufiger (alle 2–5 Min) als Charge-Point-Stammdaten (alle 30 Min)

---

## 5. PRICING ENGINE DESIGN

### Einheitensystem (verbindlich)

| Suffix | Einheit | Beispiel |
|--------|---------|----------|
| `*_cent` | Integer, Cent (1/100 EUR) | `estimated_total_30min_cent: 1180` = 11,80 € |
| `*_tenth_cent` | Integer, Zehntel-Cent (1/1000 EUR) | `per_kwh_tenth_cent: 286` = 28,6 ct/kWh |
| `*_percent` | Dezimal (0–100), eine Nachkommastelle | `percent: 68.0` |

- **Regel**: Alle Geldbeträge sind ganzzahlig. Kein Float für monetäre Werte.
- **Per-Unit-Raten** (pro kWh, pro Minute): `*_tenth_cent` – bewahrt Sub-Cent-Präzision (z.B. 28,6 ct/kWh = 286).
- **Absolute Beträge** (Startgebühr, Gesamtpreis, Rechnungssummen): `*_cent`.
- **Umrechnung**: `estimated_per_kwh_cent = round(sum(components[].per_kwh_tenth_cent) / 10)`

### Berechnungszeitpunkt
1. **Pre-Start (Estimate)**: User sieht geschätzten Preis BEVOR er startet
2. **Snapshot-Erstellung**: Beim Bestätigen des Ladevorgangs → alle Preiskomponenten eingefroren
3. **Live-Estimate**: Während laufender Session → Snapshot-Raten × bisheriger Verbrauch
4. **Final Calculation**: Nach Session-Ende: Snapshot-Raten × tatsächlicher Verbrauch → Endbetrag

### Berechnungslogik

**Eingabe**: Connector, PaymentMethod, UserSubscription (nullable), DateTime now

**Schritt 1: Tarif auflösen (StructuredTariff → ResolvedTariff)**
- StructuredTariff vom Provider-Adapter holen (aus connectors.structured_tariff_json oder live)
- TariffResolver.resolve(structuredTariff, now) → ResolvedTariff
- Ergebnis: aufgelöster pro-kWh, pro-Minute, Startgebühr, Blockiergebühr
- Bei zeitabhängigen Tarifen: validUntil zeigt an, wann sich der Tarif ändern könnte

**Schritt 2: Roaming-Gebühr**
- Aus Provider-Config: fest (z.B. 3ct/kWh) ODER prozentual (z.B. 5% auf CPO-Preis)
- Aufschlag auf Basis-Tarif

**Schritt 3: Plattform-Gebühr**
- Basis-Plattformgebühr aus system_config (z.B. 5ct/kWh oder 8%)
- MINUS Abo-Rabatt (z.B. Abo reduziert Plattformgebühr um 60%)
- Ohne Abo: volle Plattformgebühr
- Mit Abo: reduzierte Plattformgebühr (Hauptanreiz fürs Abo)

**Schritt 4: Zwischensumme**
- Subtotal = CPO (aufgelöst) + Roaming + Plattform
- Dies ist der Betrag VOR Payment-Gebühren

**Schritt 5: Payment-Gebühr (ZULETZT)**
- Aus PaymentFeeModel der gewählten Zahlungsart
- Berechnung auf Subtotal: fixed_fee + (percentage × Subtotal)
- Min/Max-Grenzen beachten (min_fee_cent, max_fee_cent)
- **Wichtig**: Payment-Fee wird auf den GESAMTBETRAG angewendet, nicht als Zwischenschritt
- Begründung: Zahlungsdienstleister berechnen Gebühren auf den Transaktionsbetrag

**Schritt 6: Gesamtpreis**
- Endpreis = Subtotal + Payment-Fee
- Geschätzter Endpreis pro kWh (basierend auf erwarteter Ladedauer/Menge)
- Zusätzlich: geschätzte Gesamtkosten für 30min und 60min Ladung

**Schritt 7: Transparenz-Kalkulation**
- Jede Komponente als Prozent vom Gesamtpreis:
  - Infrastruktur (CPO): X%
  - Roaming: Y%
  - Plattform: W%
  - Zahlungsart: Z%
  - Gesamt: 100%
- Bei zeitabhängigen Tarifen: Hinweis "Tarif gilt bis HH:MM, danach Tarif Y"
- Gespeichert als strukturiertes JSON im Snapshot
- **Rundungsregel (Prozente)**: Largest Remainder Method (Hare-Niemeyer), 1 Nachkommastelle.
  1. Berechne exakte Prozente: `component_cent / total_cent × 100`
  2. Runde alle auf 1 Nachkommastelle ab (floor)
  3. Differenz zu 100,0% auf die Komponenten mit den größten Nachkomma-Resten verteilen (+0,1%)
  4. **Cent-Werte sind Source of Truth** – Prozente sind abgeleitete Darstellungswerte

### Amortisierung von Einmalgebühren (Transparenz-Display)

Startgebühr und fixe Payment-Gebühr sind Einmalbeträge, die für die Transparenz-Anzeige (ct/kWh-Aufschlüsselung) auf die geschätzte Lademenge umgelegt werden:

```
estimated_kwh = connector_power_kw × 0.5   # Annahme: 30 Min bei Nennleistung
amortized_start_fee_tenth_cent = round(resolved_start_fee_cent × 10 / estimated_kwh)
amortized_payment_fixed_tenth_cent = round(payment_fee_fixed_cent × 10 / estimated_kwh)
```

- **Zweck**: Nur für die Preisvorschau (Transparenz-Display), NICHT für die Endabrechnung.
- **Endabrechnung**: Startgebühr und fixe Payment-Gebühr werden als absolute Beträge berechnet, nicht pro kWh.
- **Hinweis im UI**: „Einmalgebühren umgelegt auf geschätzte 30 Min Ladedauer“

### Snapshot-Speicherung
- Alle Berechnungsparameter in `pricing_snapshots` gespeichert
- IMMUTABLE: Kein UPDATE jemals erlaubt
- Enthält: ResolvedTariff + Original-StructuredTariff + alle Zwischenberechnungen
- Session verlinkt auf Snapshot via FK
- Sowohl aufgelöste Raten (für Berechnung) als auch Rohdaten (für Audit)

### Endabrechnung (Final Calculation Formula)

Nach Session-Ende wird der exakte Endbetrag aus Snapshot-Raten und tatsächlichem Verbrauch berechnet:

```
Eingabe:
  snapshot     = pricing_snapshots-Record (immutabel)
  actual_kwh   = charging_sessions.energy_kwh
  actual_min   = charging_sessions.duration_seconds / 60
  blocking_min = charging_sessions.blocking_duration_seconds / 60

Schritt 1 – CPO-Kosten (Ergebnisse in Cent):
  cpo_energy = round(snapshot.resolved_per_kwh_tenth_cent × actual_kwh / 10)
  cpo_time   = round(snapshot.resolved_per_min_tenth_cent × actual_min / 10)
  cpo_start  = snapshot.resolved_start_fee_cent
  cpo_block  = round(snapshot.resolved_blocking_fee_per_min_cent × blocking_min)
  cpo_total  = cpo_energy + cpo_time + cpo_start + cpo_block

Schritt 2 – Roaming (Cent):
  if snapshot.roaming_fee_type == 'fixed':
    roaming = round(snapshot.roaming_fee_value × actual_kwh)   # roaming_fee_value in Cent/kWh
  else:  # percentage
    roaming = round(cpo_total × snapshot.roaming_fee_value / 100)

Schritt 3 – Plattform (Cent):
  platform = round((cpo_total + roaming) × snapshot.effective_platform_fee_percent / 100)

Schritt 4 – Subtotal:
  subtotal = cpo_total + roaming + platform

Schritt 5 – Payment-Fee (ZULETZT, auf Gesamtbetrag):
  raw_fee = snapshot.payment_fee_fixed_cent + round(subtotal × snapshot.payment_fee_percentage / 100)
  payment = clamp(raw_fee, snapshot.payment_fee_min_cent, snapshot.payment_fee_max_cent)

Schritt 6 – Endbetrag:
  total_cent = subtotal + payment
```

- **Rundung**: Jeder Schritt wird einzeln auf ganzzahlige Cent gerundet (`round half up`).
- **Alle Werte in Cent (Integer)** – keine Fließkomma-Zwischenergebnisse bei Geldbeträgen.
- **Ergebnis**: `charging_sessions.total_price_cent = total_cent`

### Transparenz-Begrifflichkeit (verbindlich)

Drei klar getrennte Konzepte für Preisangaben im gesamten System:

| Begriff | Phase | Berechnung | Anzeige | Verbindlichkeit |
|---------|-------|------------|---------|------------------|
| **preview_estimate** | Vor Session-Start | Snapshot-Raten × geschätzte kWh (30/60 Min) | PricePreview-Screen | Unverbindliche Schätzung |
| **live_estimate** | Während Session | Snapshot-Raten × bisherige kWh/Minuten | Live-Session-Screen + Foreground Notification | Laufende Hochrechnung, „geschätzt“ |
| **final_total** | Nach Session-Ende | Snapshot-Raten × finale kWh/Minuten (Final Calculation Formula) | Session-Detail + Rechnung | Endgültiger Rechnungsbetrag |

- **UI-Kennzeichnung**: `preview_estimate` → „Geschätzter Preis“, `live_estimate` → „Aktuelle Kosten (geschätzt)“, `final_total` → „Endbetrag“
- **API-Felder**: Alle Response-Felder verwenden das jeweilige Präfix, z.B. `live_estimate_total_cent`, `final_total_cent`
- **Kein Float-Vergleich**: Live-Estimate und Final-Total können abweichen (wegen Rundung, Blocking-Fee etc.). User wird klar darauf hingewiesen.

### Live-Kostenschätzung (Live-Estimate)

Während einer aktiven Session wird der aktuelle Kostenstand auf Basis des immutablen Snapshots berechnet:

**Eingabe**: pricing_snapshot + aktuelle Session-Daten (vom Provider-Status oder App-Polling)

**Berechnung** (identisch zur Final Calculation, aber mit Zwischenstand-Werten):
- `current_kwh` = letzte bekannte kWh vom Provider (oder lokale Schätzung)
- `current_duration_sec` = now() - session.started_at
- `blocking_sec` = 0 (während Ladung läuft keine Blockiergebühr)
- Berechnung exakt wie Final Calculation Formula (Schritte 1–6), aber mit `current_kwh` / `current_duration_sec`

**Datenquellen-Hierarchie**:
1. **Provider-Status**: Wenn `getSessionStatus()` aktuelle kWh liefert → höchste Priorität
2. **Schätzung auf Basis Connector-Leistung**: Wenn Provider keine kWh liefert → `estimated_kwh = connector_power_kw × (current_duration_sec / 3600)`
3. **Dauer allein**: Wenn nur Zeitkomponente im Tarif → rein zeitbasierte Kosten

**Kennzeichnung bei unvollständigen Daten**:
- `data_source: "provider"` – kWh vom Provider bestätigt
- `data_source: "estimated"` – kWh auf Basis Connector-Leistung geschätzt
- App zeigt bei `estimated`: „Kosten basierend auf Schätzung – tatsächlicher Wert kann abweichen“

**Aktualisierungsfrequenz**:
- Backend berechnet Live-Estimate NICHT proaktiv, sondern auf API-Request (GET /charging/active/live-status)
- App pollt alle 30 Sekunden (konfigurierbarer Riverpod-Timer)
- Push-Benachrichtigungen mit Live-Kosten nur bei definierten Events (siehe MVP-Regeln)

### Tarif-Auflösung (TariffResolver)
- **Simple Tarife** (1 Element, keine Restrictions): Direkte Übernahme → tariff_type=simple
- **Zeitabhängige Tarife** (mehrere Elemente mit Zeitfenstern): Aktuelles Element wählen → tariff_type=time_based
- **Gestaffelte Tarife** (abhängig von kWh/Dauer): Basierend auf Schätzung des Verbrauchs → tariff_type=tiered
- **Flat-Rate**: Umgerechnet auf geschätzte kWh (Connector-Power als Basis)
- **Fallback**: Kein passendes Element → Element ohne Restrictions nehmen oder Fehler

### Preisanzeige für User (PricePreview)

```
Geschätzter Preis: 42 ct/kWh

┌─────────────────────────────────────────────┐
│ Preiszusammensetzung                        │
│                                             │
│  Infrastruktur (CPO)   28,6 ct/kWh   68%   │
│  Roaming-Netzwerk       3,4 ct/kWh    8%   │
│  Plattform              8,4 ct/kWh   20%   │
│  Zahlungsart (Visa)     1,6 ct/kWh    4%   │
│  ──────────────────────────────────         │
│  Gesamt                42,0 ct/kWh  100%   │
│                                             │
│  ⚡ Geschätzte Kosten:                      │
│     30 Min Ladung: ~11,80 €                 │
│     60 Min Ladung: ~21,40 €                 │
│                                             │
│  💡 Spare 12% mit Abo Premium               │
│     → Plattformgebühr: 8,4 → 3,4 ct/kWh   │
│                                             │
│  ⏰ Zeithinweis (bei zeitabh. Tarif):       │
│     "Aktueller Tarif gilt bis 20:00.        │
│      Ab 20:00: Nachttarif 35 ct/kWh"       │
│                                             │
│  ⚠️ Blockiergebühr nach Ladeende:           │
│     5 ct/Min ab 15 Min nach Ladeschluss     │
└─────────────────────────────────────────────┘
```

### PricePreview API-Response (POST /charging/calculate-price)

```json
{
  "status": "success",
  "data": {
    "estimated_per_kwh_cent": 42,
    "estimated_30min_cent": 1180,
    "estimated_60min_cent": 2140,
    "tariff_type": "time_based",
    "is_time_dependent": true,
    "current_tariff_valid_until": "2026-04-08T20:00:00+02:00",
    "components": [
      { "name": "cpo", "label": "Infrastruktur (CPO)", "per_kwh_tenth_cent": 286, "percent": 68.0 },
      { "name": "roaming", "label": "Roaming-Netzwerk", "per_kwh_tenth_cent": 34, "percent": 8.0 },
      { "name": "platform", "label": "Plattform", "per_kwh_tenth_cent": 84, "percent": 20.0 },
      { "name": "payment", "label": "Zahlungsart (Visa)", "per_kwh_tenth_cent": 16, "percent": 4.0 }
    ],
    "blocking_fee": {
      "per_minute_cent": 5,
      "grace_period_minutes": 15
    },
    "subscription_hint": {
      "potential_saving_percent": 12,
      "platform_fee_with_sub_per_kwh_tenth_cent": 34,
      "plan_name": "Premium"
    },
    "time_tariff_hint": {
      "next_tariff_label": "Nachttarif",
      "next_tariff_per_kwh_cent": 35,
      "changes_at": "2026-04-08T20:00:00+02:00"
    }
  }
}
```

---

## 6. BILLING / LEXWARE INTEGRATION

### Architektur

**BillingService** (orchestriert):
- Reagiert auf Session-Completion
- Berechnet Endbetrag: Snapshot-Raten × tatsächliche kWh/Zeit
- Erstellt lokale Invoice-Record
- Übergibt an LexwareAdapter

**LexwareAdapterInterface**:
```
- createInvoice(InvoiceData): LexwareInvoiceResponse
- getInvoice(lexwareId): LexwareInvoiceDetail
- getInvoicePdf(lexwareId): binary (PDF)
- cancelInvoice(lexwareId): bool
```

**LexwareAdapter**: REST-API-Client für Lexware, mappt interne InvoiceData → Lexware-Format.

**MockLexwareAdapter**: Für lokale Entwicklung/Tests, generiert Dummy-PDF.

### Sync-Strategie (Flow)
1. Session → status=completed
2. BillingService → berechnet Endbetrag aus Snapshot + actuals
3. Invoice lokal erstellen (status: pending)
4. LexwareAdapter.createInvoice() aufrufen
5. Erfolg → lexware_invoice_id speichern, status: created
6. LexwareAdapter.getInvoicePdf() → PDF in `writable/invoices/{year}/{month}/{invoice_id}.pdf`
7. Invoice pdf_path updaten, status: sent

### Fehlerhandling
- Lexware-API nicht erreichbar → Invoice bleibt status: pending
- Cron-Job: `php spark billing:retry-pending` alle 15 Min
- Exponentielles Backoff: 15min, 30min, 1h, 2h, 4h, 8h
- Max 10 Retries → danach status: failed + Admin-Notification
- Admin kann manuell Retry auslösen

### Abo-Rechnungen
- Separater Cron: `php spark billing:subscription-invoices`
- Prüft monatlich/jährlich fällige Abos
- Erstellt Invoice mit invoice_type=subscription
- Gleicher Lexware-Flow

### PDF-Handling
- Lokal gespeichert: `writable/invoices/{YYYY}/{MM}/{invoice_id}.pdf`
- In DB nur Pfad gespeichert
- API-Endpunkt streamt PDF direkt
- PDFs nicht öffentlich zugänglich (kein public/-Ordner)

---

## 6a. PUSH & NOTIFICATION SYSTEM

### Push-Infrastruktur

**Technologie**: Firebase Cloud Messaging (FCM) HTTP v1 API
- Einheitlich für Android + iOS
- Backend sendet über FCM HTTP v1 (OAuth2 Service-Account, nicht Legacy-API)
- Kein Firebase SDK im Backend – reiner REST-Client (CI4 CURLRequest)
- Flutter-App nutzt `firebase_messaging` Package für Token-Empfang + Nachrichtenverarbeitung

**NotificationService** (Backend, orchestriert):
- Empfängt interne Events (Session-Status, Blocking-Warning, etc.)
- Prüft Nutzerpräferenzen (notification_preferences)
- Lädt aktive Geräte des Users (user_devices)
- Baut plattform-spezifische Payloads (Android: data message, iOS: notification + data)
- Sendet via FCM HTTP v1 an alle aktiven Geräte des Users
- Persistiert Versandversuch + Ergebnis

### Token-Lifecycle

1. **Registrierung**: App startet → `firebase_messaging.getToken()` → POST /user/devices/register mit {platform, push_token, app_version, device_name}
2. **Token-Refresh**: FCM kann Token jederzeit erneuern → `onTokenRefresh` Callback → PUT /user/devices/{id} mit neuem Token
3. **App-Start**: Bei jedem App-Start `last_seen_at` aktualisieren + Token prüfen/erneuern
4. **Logout**: App ruft DELETE /user/devices/{id} → Token gelöscht
5. **Invalidierung**: FCM meldet `UNREGISTERED` → Backend setzt is_active=false
6. **Cleanup**: Cron `php spark devices:cleanup` löscht Geräte mit last_seen_at > 90 Tage

### Event → Push Mapping

Events werden von Fachservices erzeugt und vom NotificationService in Push-Nachrichten umgewandelt:

| Quelle | Event | Push-Typ | Payload |
|--------|-------|----------|--------|
| ChargingService | `session_started` | Einmalig | Status, Connector-Info, Tarifübersicht |
| ChargingService (Status-Poll) | `session_active` (erster Energiefluss) | Einmalig | „Ladung aktiv“, erste kWh, Live-Kosten |
| ChargingService (Status-Poll) | `session_paused` | Einmalig | Status, bisherige kWh + Kosten |
| ChargingService | `session_completed` | Einmalig | Finale kWh, Dauer, **final_total** |
| ChargingService | `session_failed` | Einmalig | Fehlergrund, Handlungsempfehlung |
| LiveCostService | `session_live_cost` | Periodisch (MVP-Regeln) | Aktuelle kWh, Dauer, **live_estimate**, data_source |
| ChargingService (Timer) | `blocking_fee_warning` | Einmalig | „Blockiergebühr startet in 5 Min“ |
| ChargingService (Timer) | `blocking_fee_active` | Einmalig | „Blockiergebühr läuft“, aktueller Betrag |
| ProviderSyncService | `station_available` | Einmalig | Station-Name, Connector-Status |
| BillingService | `invoice_ready` | Einmalig | Rechnungsnummer, Betrag, PDF-Link |

### Entkopplung Fachlogik ↔ Push-Versand

**Ablauf**:
1. Fachservice (z.B. ChargingService) schreibt `session_events`-Record
2. Fachservice ruft `NotificationService::dispatch(userId, eventType, eventData)` auf
3. NotificationService prüft `notification_preferences` für diesen User + event_type
4. Wenn enabled: baut Payload, sendet an alle aktiven Geräte
5. Wenn disabled: kein Push, aber session_event bleibt persistiert

**Wichtig**: Session-Events werden IMMER geschrieben, unabhängig von Push-Präferenzen. Push ist ein Seiteneffekt, nicht die Primäraktion.

### Push-Retry-Strategie

- Erster Versuch: sofort (synchron im Request-Context)
- FCM timeout oder 5xx → notification_queue-Eintrag (DB-Queue)
- Cron: `php spark notifications:retry-pending` alle 2 Min
- Max. 3 Retries mit Backoff: 2 Min, 10 Min, 30 Min
- Nach 3 Fehlschlägen: verworfen (kein weiterer Retry – Push ist best-effort)
- Bei `UNREGISTERED` / `INVALID_ARGUMENT`: kein Retry, Device deaktivieren

### MVP-Regeln für Live-Kosten-Push

Live-Kosten werden NICHT bei jedem Polling-Zyklus als Push gesendet. Stattdessen nur bei definierten Schwellen:

| Trigger | Bedingung | Push-Inhalt |
|---------|-----------|-------------|
| Session-Start | Immer | Startzeitpunkt, Tarif, geschätzte Kosten |
| Erster Energiefluss | energy_kwh > 0 erstmals | „Ladung aktiv“, erste kWh |
| Kostensprung | live_estimate überschreitet nächste volle Euro-Schwelle (1€, 2€, 5€, 10€, 20€…) | Aktueller Betrag |
| Periodisch | Alle 10 Minuten während aktiver Ladung | kWh, Dauer, live_estimate |
| Blocking-Warnung | 5 Min vor grace_period_minutes-Ende | „Bitte Fahrzeug abstecken“ |
| Blocking aktiv | Blocking-Fee läuft | Blockier-Kosten pro Minute |
| Session-Ende | Immer | Finale kWh, Dauer, final_total |

**Euro-Schwellen-Logik**: Backend trackt `last_push_cost_threshold_cent` pro Session. Wenn `live_estimate_total_cent >= last_threshold + schwelle` → Push + Threshold aktualisieren. Schwellen: 100, 200, 500, 1000, 2000 Cent (danach alle 2000).

---

## 7. API DESIGN STRATEGIE

### Versionierung
- URL-basiert: `/api/v1/...`
- Neue Versionen parallel betreibbar
- CI4 Route Groups mit Namespace-Prefix

### Auth Endpunkte (öffentlich)

| Method | Endpunkt | Beschreibung |
|--------|----------|--------------|
| POST | /api/v1/auth/register | Registrierung |
| POST | /api/v1/auth/login | Login → JWT |
| POST | /api/v1/auth/refresh | Token erneuern |
| POST | /api/v1/auth/logout | Token invalidieren |
| POST | /api/v1/auth/forgot-password | Passwort-Reset anfordern |
| POST | /api/v1/auth/reset-password | Passwort zurücksetzen |
| GET  | /api/v1/auth/verify-email/{token} | E-Mail bestätigen |

### User Endpunkte (authentifiziert)

| Method | Endpunkt | Beschreibung |
|--------|----------|--------------|
| GET | /api/v1/user/profile | Profil abrufen |
| PUT | /api/v1/user/profile | Profil aktualisieren |
| DELETE | /api/v1/user/account | Account löschen (DSGVO) |
| GET | /api/v1/user/payment-methods | Zahlungsarten listen |
| POST | /api/v1/user/payment-methods | Zahlungsart hinzufügen |
| DELETE | /api/v1/user/payment-methods/{id} | Zahlungsart entfernen |
| PUT | /api/v1/user/payment-methods/{id}/default | Als Standard setzen |
| GET | /api/v1/user/data-export | DSGVO Datenexport |
| POST | /api/v1/user/devices/register | Push-Gerät registrieren (FCM-Token) |
| PUT | /api/v1/user/devices/{id} | Push-Token aktualisieren |
| DELETE | /api/v1/user/devices/{id} | Gerät abmelden (Logout) |
| GET | /api/v1/user/devices | Registrierte Geräte listen |
| GET | /api/v1/user/notification-preferences | Benachrichtigungspräferenzen abrufen |
| PUT | /api/v1/user/notification-preferences | Präferenzen aktualisieren (Batch: Array von {event_type, enabled}) |

### Subscription Endpunkte (authentifiziert)

| Method | Endpunkt | Beschreibung |
|--------|----------|--------------|
| GET | /api/v1/subscriptions/plans | Verfügbare Pläne |
| GET | /api/v1/subscriptions/current | Aktuelles Abo |
| POST | /api/v1/subscriptions | Abo abschließen |
| PUT | /api/v1/subscriptions/cancel | Abo kündigen |

### Charging Endpunkte (authentifiziert)

| Method | Endpunkt | Beschreibung |
|--------|----------|--------------|
| GET | /api/v1/chargepoints | Ladepunkte suchen (Geo + Filter) |
| GET | /api/v1/chargepoints/{id} | Ladepunkt-Details mit Connectors |
| POST | /api/v1/charging/calculate-price | Preis vorab berechnen |
| POST | /api/v1/charging/start | Ladevorgang starten |
| POST | /api/v1/charging/stop | Ladevorgang stoppen |
| GET | /api/v1/charging/active | Aktive Session |
| GET | /api/v1/charging/sessions | Session-Historie |
| GET | /api/v1/charging/sessions/{id} | Session-Details |
| GET | /api/v1/charging/active/live-status | Live-Status + Live-Kosten der aktiven Session |

### Billing Endpunkte (authentifiziert)

| Method | Endpunkt | Beschreibung |
|--------|----------|--------------|
| GET | /api/v1/invoices | Rechnungsliste |
| GET | /api/v1/invoices/{id} | Rechnungsdetails |
| GET | /api/v1/invoices/{id}/pdf | PDF herunterladen |

### Admin Endpunkte (Admin-Auth, zweistufig mit TOTP)

| Method | Endpunkt | Beschreibung |
|--------|----------|--------------|
| POST | /api/v1/admin/auth/login | Admin-Login Stufe 1: E-Mail + Passwort → temporäres pre-auth Token |
| POST | /api/v1/admin/auth/totp-verify | Admin-Login Stufe 2: pre-auth Token + TOTP-Code → vollständiges JWT |
| POST | /api/v1/admin/auth/totp-setup | TOTP einrichten (für Status totp_pending): generiert Secret + QR-URI |
| POST | /api/v1/admin/auth/totp-confirm | TOTP-Setup bestätigen: überprüft Code, aktiviert TOTP, liefert Recovery-Codes |
| POST | /api/v1/admin/auth/totp-recovery | Login mit Recovery-Code (statt TOTP) |
| GET | /api/v1/admin/dashboard | Dashboard-Daten |
| GET | /api/v1/admin/users | Nutzerliste |
| GET | /api/v1/admin/users/{id} | Nutzer-Detail |
| PUT | /api/v1/admin/users/{id}/block | Nutzer sperren |
| GET | /api/v1/admin/providers | Provider-Liste |
| POST | /api/v1/admin/providers | Provider hinzufügen |
| PUT | /api/v1/admin/providers/{id} | Provider bearbeiten |
| POST | /api/v1/admin/providers/{id}/sync | Manueller Sync |
| GET | /api/v1/admin/sessions | Alle Sessions |
| GET | /api/v1/admin/invoices | Alle Rechnungen |
| POST | /api/v1/admin/invoices/{id}/retry | Rechnung Retry |
| GET | /api/v1/admin/config | System-Konfiguration |
| PUT | /api/v1/admin/config | Config aktualisieren |
| GET | /api/v1/admin/audit-log | Audit-Log |
| POST | /api/v1/admin/admins/invite | Neuen Admin einladen (nur super_admin) |
| GET | /api/v1/admin/admins | Admin-Liste (nur super_admin) |
| PUT | /api/v1/admin/admins/{id}/block | Admin sperren (nur super_admin) |
| PUT | /api/v1/admin/admins/{id}/reset-totp | TOTP zurücksetzen → Status auf totp_pending, totp_secret + recovery_codes gelöscht (nur super_admin) |

### Auth Flow
- **Access Token**: JWT, 15 Minuten Laufzeit, enthält user_id + role
- **Refresh Token**: Opaque Token (gehashed in DB), 30 Tage, Device-gebunden
- **Flow**: Login → Access + Refresh → Bearer Header → bei Ablauf Refresh → neues Paar
- **Admin**: Separater JWT-Claim (role: admin), separate User-Tabelle

### Admin Auth Flow (Zweistufig mit TOTP)

**Stufe 1 – Passwort-Prüfung:**
1. POST /admin/auth/login mit {email, password}
2. Passwort korrekt → temporäres `pre_auth_token` (JWT, 5 Min Laufzeit, Claim: `stage: "pre_auth"`)
3. Passwort falsch → Rate-Limiting, Audit-Log
4. Admin-Status `totp_pending` → Response enthält `requires_totp_setup: true` (Client leitet zum Setup)
5. Admin-Status `blocked` → Login verweigert

**Stufe 2a – TOTP-Verifizierung (normaler Login):**
1. POST /admin/auth/totp-verify mit {pre_auth_token, totp_code}
2. Code korrekt → vollständiges Admin-JWT (15 Min) + Refresh-Token
3. Code falsch → max. 5 Versuche pro pre_auth_token, danach ungültig

**Stufe 2b – TOTP-Ersteinrichtung (einmalig):**
1. POST /admin/auth/totp-setup mit {pre_auth_token}
2. Generiert TOTP-Secret (verschlüsselt gespeichert) + otpauth:// URI für QR-Code
3. POST /admin/auth/totp-confirm mit {pre_auth_token, totp_code}
4. Code korrekt → TOTP aktiviert, 10 Recovery-Codes generiert (einmalig angezeigt), Status → active
5. Response enthält Recovery-Codes + vollständiges JWT

**Recovery-Flow:**
- POST /admin/auth/totp-recovery mit {pre_auth_token, recovery_code}
- Recovery-Code einmalig gültig (nach Nutzung invalidiert)
- Max. 10 Codes, danach Admin muss von super_admin entsperrt + TOTP zurückgesetzt werden

### Response-Struktur (einheitlich)

**Erfolg:**
```json
{
  "status": "success",
  "data": { },
  "meta": { "page": 1, "per_page": 20, "total": 142 }
}
```

**Fehler:**
```json
{
  "status": "error",
  "code": "VALIDATION_ERROR",
  "message": "Validierung fehlgeschlagen",
  "errors": { "email": ["E-Mail ist erforderlich"] }
}
```

**Error Codes**: VALIDATION_ERROR, AUTH_FAILED, TOKEN_EXPIRED, FORBIDDEN, NOT_FOUND, SESSION_ACTIVE, SESSION_NOT_FOUND, PROVIDER_ERROR, BILLING_ERROR, RATE_LIMITED, INTERNAL_ERROR

### Live-Status API-Response (GET /charging/active/live-status)

```json
{
  "status": "success",
  "data": {
    "session_id": 42,
    "session_status": "active",
    "started_at": "2026-04-09T14:22:00+02:00",
    "duration_seconds": 1800,
    "energy_kwh": 12.4,
    "data_source": "provider",
    "live_estimate": {
      "total_cent": 521,
      "label": "Aktuelle Kosten (geschätzt)",
      "components": [
        { "name": "cpo", "label": "Infrastruktur", "amount_cent": 355 },
        { "name": "roaming", "label": "Roaming", "amount_cent": 42 },
        { "name": "platform", "label": "Plattform", "amount_cent": 100 },
        { "name": "payment", "label": "Zahlungsart", "amount_cent": 24 }
      ]
    },
    "blocking_info": {
      "is_blocking": false,
      "grace_period_remaining_seconds": null,
      "blocking_cost_cent": 0
    },
    "tariff_info": {
      "tariff_type": "time_based",
      "current_tariff_valid_until": "2026-04-09T20:00:00+02:00",
      "hint": "Aktueller Tarif gilt bis 20:00"
    },
    "snapshot_id": 42
  }
}
```

- **Kein `final_total`-Feld**: Während aktiver Session gibt es nur `live_estimate`. `final_total` erscheint erst nach Session-Ende im Session-Detail.
- **`data_source`**: Zeigt dem Client ob kWh vom Provider (`provider`) oder lokal geschätzt (`estimated`)
- **Polling-Empfehlung**: App pollt alle 30 Sek. Header `X-Poll-Interval: 30` in Response für serverseitige Steuerung.

### CI4-Implementierungsstrategie
- BaseApiController extends ResourceController mit ResponseTrait
- API-Filter: JwtAuthFilter, AdminAuthFilter, RateLimitFilter, CorsFilter
- Route Groups: `$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], ...)`
- Controller-Namespaces: App\Controllers\Api\V1\AuthController, etc.

---

## 8. MOBILE APP ARCHITEKTUR (Flutter)

### State Management: Riverpod 2.x
- **Begründung**: Type-safe, testbar, weniger Boilerplate als BLoC, gute Async-Unterstützung
- **Alternative verworfen**: BLoC – mehr Boilerplate für gleiches Ergebnis

### Layer-Struktur

```
lib/
├── main.dart
├── app.dart                   # MaterialApp, Routing, Theme
├── core/
│   ├── api/
│   │   ├── api_client.dart    # Dio-basierter HTTP-Client
│   │   ├── api_interceptors.dart  # Auth-Token, Error-Handling
│   │   └── api_endpoints.dart
│   ├── auth/
│   │   ├── auth_provider.dart # Globaler Auth-State
│   │   └── token_storage.dart # Secure Storage für Tokens
│   ├── config/
│   │   └── app_config.dart
│   ├── errors/
│   │   ├── app_exception.dart
│   │   └── error_handler.dart
│   ├── routing/
│   │   └── app_router.dart    # GoRouter
│   └── theme/
│       └── app_theme.dart
├── features/
│   ├── auth/
│   │   ├── data/              # Repository, DTOs
│   │   ├── providers/         # Riverpod Providers
│   │   └── presentation/     # Screens, Widgets
│   ├── home/
│   │   ├── data/
│   │   ├── providers/
│   │   └── presentation/     # Map-View, Charge-Point-Liste
│   ├── charging/
│   │   ├── data/
│   │   ├── providers/
│   │   └── presentation/     # Preis-Anzeige, Session-Steuerung, Live-Kosten-View
│   ├── notifications/
│   │   ├── data/              # Push-Token-Repository, Präferenz-Repository
│   │   ├── providers/         # Push-Token-Provider, Präferenz-Provider
│   │   └── presentation/     # Einstellungen-Screen für Benachrichtigungen
│   ├── profile/
│   │   ├── data/
│   │   ├── providers/
│   │   └── presentation/     # Profil, Zahlungsarten
│   ├── subscription/
│   │   ├── data/
│   │   ├── providers/
│   │   └── presentation/     # Plan-Auswahl, Abo-Status
│   └── invoices/
│       ├── data/
│       ├── providers/
│       └── presentation/     # Rechnungsliste, PDF-Viewer
└── shared/
    ├── widgets/               # Reusable UI-Komponenten
    ├── models/                # Shared Data Models (Freezed)
    └── utils/
```

### Packages
- **HTTP**: dio + dio_interceptors
- **State**: flutter_riverpod + riverpod_annotation
- **Routing**: go_router
- **Storage**: flutter_secure_storage (Tokens), hive (Cache)
- **Maps**: flutter_map (OpenStreetMap – kostenlos, DSGVO-freundlich)
- **PDF**: flutter_pdfview
- **Models**: freezed + json_serializable
- **Geolocation**: geolocator

### Push-Notification-Integration (Flutter)

**Package**: `firebase_messaging` + `flutter_local_notifications`

**Initialisierung**:
1. App-Start: `FirebaseMessaging.instance.getToken()` → POST /user/devices/register
2. `FirebaseMessaging.onTokenRefresh` → PUT /user/devices/{id}
3. Permission-Request: Bei erster Nutzung (iOS erfordert explizite Erlaubnis)

**Nachrichtenverarbeitung**:
- **Foreground**: `FirebaseMessaging.onMessage` → In-App-Overlay oder lokale Notification
- **Background**: `FirebaseMessaging.onBackgroundMessage` → System-Notification via `flutter_local_notifications`
- **Terminated**: System-Notification (FCM default), Tap öffnet App mit Deep-Link

**Plattform-Unterschiede**:
- **Android**: Data Message bevorzugt (volle Kontrolle über Darstellung). Foreground Service für laufende Session-Notification (persistent, nicht wegwischbar).
- **iOS**: Notification + Data Message (iOS zeigt Data-only nicht im Background). Kein nativer Foreground Service – stattdessen periodische Push-Updates + lokale Timer-basierte Notification.

### Laufende Session-Benachrichtigung (Foreground Notification)

Während einer aktiven Session zeigt die App eine persistente Benachrichtigung:

**Android** (Foreground Service):
- Persistente Notification (nicht wegwischbar, während Session aktiv)
- Aktualisiert sich alle 30 Sek (via Polling-Response oder Push)
- Zeigt: Aktuelle Kosten, kWh, Dauer, Status
- Tap → öffnet Live-Session-Screen
- Endet automatisch bei Session-Completion/Failure

**iOS** (Lokale Notification + Push):
- Kein Foreground Service möglich → Kombination aus:
  - Push-Nachrichten mit `content-available` für stille Updates
  - LiveActivity (iOS 16.1+) für Dynamic-Island / Lock-Screen-Widget (Post-MVP)
  - Lokale Notifications bei relevanten Events (Kostensprung, Blocking-Warnung)
- Im Foreground: In-App-Banner mit Live-Daten (Riverpod-State, Polling-driven)

**Notification-Inhalt** (Beispiel):
```
⚡ Ladung aktiv – 42 Min
12,4 kWh • ~5,21 € (geschätzt)
Infrastruktur 68% • Roaming 8% • Plattform 20% • Zahlung 4%
```

**Datenfluss App**:
1. App pollt GET /charging/active/live-status alle 30 Sek
2. Response aktualisiert Riverpod liveSessionProvider
3. UI (Live-Session-Screen) und Foreground Notification lesen selben Provider
4. Push-Nachrichten (falls App im Background) aktualisieren zusätzlich den lokalen State

### Offline / Caching Strategie
- **Charge-Points**: Hive-Cache, Time-based Invalidation (5 Min), Map-Tiles offline via OSM
- **User Profile + Subscription**: Hive-Cache, aktualisiert bei App-Start
- **Session-Historie**: Lokale DB (Hive), Sync bei Verbindung
- **Invoices**: Metadaten cached, PDFs on-demand heruntergeladen + lokal gespeichert
- **Kein Offline-Start**: Ladung starten erfordert zwingend Online-Verbindung
- **Live-Session-Daten**: Nicht gecached – immer live vom Server (Polling)

---

## 9. CI/CD STRATEGIE

### Backend Pipeline (Jenkins)

**Job**: `de.einfach-laden` (existiert bereits)

**Pipeline-Schritte:**
1. Git Checkout (main/develop Branch)
2. `composer install --no-dev --optimize-autoloader`
3. PHP Linting: `php -l` auf alle PHP-Dateien
4. PHPUnit: `vendor/bin/phpunit`
5. Deploy: SSH → `cd /srv/www/git/de.einfach-laden && git pull && php spark migrate`
6. Post-Deploy: Smoke-Test (curl auf Health-Endpunkt)

**Jenkinsfile**: Deklarative Pipeline, Stages: Checkout → Install → Lint → Test → Deploy. Deploy nur aus main-Branch.

### Mobile Pipeline (Jenkins)

**Android** (Linux/Windows Agent):
1. `flutter pub get`
2. `flutter analyze`
3. `flutter test`
4. `flutter build appbundle --release`
5. Artifact archivieren (.aab)
6. (Später: fastlane → Play Store Upload)

**iOS** (Mac Mini Agent – Vorbereitung):
1. Mac Mini als Jenkins Agent einrichten (JNLP oder SSH)
2. Xcode + Flutter SDK installieren
3. Apple Developer Certificates/Profiles konfigurieren
4. `flutter build ipa --release`
5. Artifact archivieren (.ipa)

### Branching-Strategie
- **main**: Produktiv-Code, geschützt, nur über Merge
- **develop**: Integrations-Branch
- **feature/{name}**: Feature-Branches aus develop
- **hotfix/{name}**: Hotfixes aus main

### Artefakte
- Backend: Kein Build-Artefakt (PHP, direkt deploybar)
- Android: .aab (Play Store), .apk (Testing)
- iOS: .ipa (App Store)

---

## 10. SICHERHEIT & COMPLIANCE

### Authentifizierung
- JWT (RS256 oder HS256 mit starkem Secret)
- Passwörter: password_hash() mit PASSWORD_ARGON2ID
- Rate Limiting: Login max 5 Versuche/Minute (IP-basiert)
- Refresh Token Rotation: Bei Nutzung wird altes Token invalidiert

### Daten-Sicherheit
- Provider-Credentials: AES-256-GCM verschlüsselt in DB (CI4 Encryption Service)
- Payment-Referenzen: Verschlüsselt in DB
- Alle API-Kommunikation: HTTPS erzwungen (ForceHTTPS Filter)
- SQL Injection: CI4 Query Builder (Prepared Statements)
- XSS: API gibt JSON aus → kein HTML-Rendering
- CORS: Restriktiv konfiguriert

### API-Sicherheit
- Input-Validierung auf jedem Endpunkt (CI4 Validation)
- Request-Body-Größe limitiert
- Keine sensiblen Daten in URLs/Logs
- API-Keys für Admin-Endpunkte zusätzlich zum JWT

### Logging
- Security-Events: Login, Failed Login, Token Refresh, Password Reset, Account Deletion
- Audit-Log: Alle schreibenden Operationen mit Actor, IP, Timestamp
- Keine sensiblen Daten in Logs
- Log-Rotation: CI4 Logger mit täglicher Rotation

### DSGVO
- **Einwilligung**: Explizites Opt-in bei Registrierung, Version der Datenschutzerklärung gespeichert
- **Datenminimierung**: Nur notwendige Daten erheben
- **Auskunftsrecht**: GET /api/v1/user/data-export → JSON-Export aller Nutzerdaten
- **Löschrecht**: DELETE /api/v1/user/account → Soft-Delete + 30 Tage Frist → Hard-Delete Cron
- **Datenportabilität**: Export in maschinenlesbarem Format (JSON)
- **Datenspeicherung**: EU-Server (profipos.de → DE)
- **Aufbewahrungspflicht**: Rechnungen 10 Jahre → Pseudonymisierung bei Account-Löschung

---

## 11. SKALIERUNG & PERFORMANCE

### Sofortige Optimierungen
- **Spatial Index** auf charge_points(latitude, longitude) für Geo-Queries
- **Composite Indexes**: (user_id, status) auf sessions, (provider_id, external_id) auf charge_points
- **Query-Caching**: Charge-Point-Daten in CI4 File-Cache (5 Min TTL)
- **Pagination**: Alle Listen-Endpunkte mit Cursor- oder Offset-Pagination
- **Eager Loading**: Connectors mit Charge-Points, Snapshots mit Sessions

### Spätere Skalierung
- **Redis**: Session-Cache, Rate-Limiting, Queue für Async-Tasks
- **Read-Replica**: Für lesende Admin-Queries und Charge-Point-Suche
- **Queue-System**: Billing, Provider-Sync, E-Mail-Versand
- **CDN**: Statische Assets, Invoice-PDFs
- **Horizontal**: Load Balancer + mehrere PHP-Instanzen (Stateless API)

### DB-Strategien
- Connection Pooling (MariaDB max_connections tunen)
- Slow-Query-Log aktivieren
- Partitioning auf audit_log (nach created_at, monatlich)
- Archive-Strategie für alte Sessions/Invoices (nach 2 Jahren)

---

## 12. RISIKEN & TECHNISCHE HERAUSFORDERUNGEN

### Kritisch

| # | Risiko | Mitigation | Architektur |
|---|--------|------------|-------------|
| 1 | Tarif ändert sich zwischen Vorschau und Start | Snapshot nur beim Start, Vorschau ist Schätzung | Snapshot-Tabelle IMMUTABLE |
| 2 | Provider-API-Ausfall | Retry mit Timeout, klare Fehlermeldung | Circuit-Breaker im Adapter |
| 3 | Inkonsistente Sessions (lokal vs Provider) | Zwei-Phasen: lokal pending → Provider start → lokal active | Recovery-Cron für stale Sessions |
| 4 | Lexware-Ausfall | Lokale Invoice = Truth, Retry-Queue, Admin-Alarm | DB-Queue mit Backoff |

### Hoch

| # | Risiko | Mitigation |
|---|--------|------------|
| 5 | Tarif-Normalisierung fehlerhaft | Robuste Konvertierung, Unit-Tests pro Adapter, Logging für unbekannte Formate |
| 6 | Session-Status-Updates verzögert | Polling mit Timeout, User kann manuell beenden |
| 7 | Zahlungsdaten-Sicherheit | Verschlüsselung, minimale Datenhaltung, keine vollständigen Kartennummern |
| 7a | Tarifwechsel während laufender Session | Snapshot-Tarif gilt vollständig (E15). User wird VOR Start über zeitabh. Tarif informiert (PricePreview-Hinweis). Post-MVP: Pro-rata evaluieren |

### Mittel

| # | Risiko | Mitigation |
|---|--------|------------|
| 8 | Alte App nutzt veraltete API | API v1 stabil halten, Breaking Changes nur in v2 |
| 9 | DSGVO-Löschung vs. Aufbewahrungspflicht | Pseudonymisierung, Rechnungsdaten behalten |
| 10 | FCM-Token ungültig / Push nicht zugestellt | Token-Invalidierung bei UNREGISTERED, Device-Cleanup-Cron, Push ist best-effort (kein geschäftskritischer Kanal) |
| 11 | Live-Kosten weichen vom Endbetrag ab | Klare UI-Kennzeichnung „geschätzt“, unterschiedliche Begriffe (live_estimate vs. final_total), Abweichungshinweis nach Session-Ende |
| 12 | Provider liefert keine Zwischen-kWh | Fallback auf Schätzung (Connector-Power), UI-Kennzeichnung `data_source: estimated` |
| 13 | Zu viele Push-Nachrichten nerven Nutzer | Schwellen-basierte Logik (nicht jede Sekunde), konfigurierbare Präferenzen pro Event-Type, Opt-out möglich |
| 14 | Android Foreground Service Batterieverbrauch | Polling-Intervall 30 Sek (nicht 1 Sek), Service endet automatisch bei Session-Ende |

---

## 13. IMPLEMENTIERUNGSPLAN (Phasen)

### Abhängigkeitsdiagramm

```
Phase 1 (Core) ──┬──► Phase 2 (Auth/Users) ──► Phase 3 (Subscriptions) ──┬──► Phase 5 (Pricing)
                  │                                                        │
                  └──► Phase 4 (Providers) ───────────────────────────────┘
                                                                                 │
                                                                    Phase 6 (Sessions) ──► Phase 7 (Billing) ──► Phase 8 (Admin)
                                                                           │
                                                                    Phase 9 (Flutter App, parallel ab hier)
                  Phase 10 (CI/CD, inkrementell ab Phase 1)
```

Phase 3 und 4 sind **parallel** ausführbar. Phase 5 blockiert auf beide. Phase 9 kann ab Phase 6 parallel beginnen.

### Phase 1: Core Backend Infrastruktur
**Abhängigkeiten**: Keine (Start)

1. Verzeichnisstruktur anlegen:
   - app/Controllers/Api/V1/
   - app/Libraries/{Providers,Billing,Pricing}/Contracts/
   - app/Libraries/{Providers,Billing,Pricing}/Adapters/
   - app/Entities/
2. BaseApiController mit ResponseTrait erstellen
3. Einheitliche Response-Helper (success, error, paginated)
4. CI4 Error/Exception Handler für API-Responses konfigurieren
5. .env Konfiguration (DB, Encryption Key, JWT Secret)
6. Database.php konfigurieren (MariaDB)
7. CORS-Filter konfigurieren
8. Health-Check Endpunkt: GET /api/v1/health

### Phase 2: Auth & Users
**Abhängigkeiten**: Phase 1

1. Migration: users-Tabelle
2. Migration: user_refresh_tokens-Tabelle
3. Migration: admin_users-Tabelle (inkl. TOTP-Felder: totp_secret_encrypted, totp_verified_at, recovery_codes_encrypted, Status-Machine: invited→totp_pending→active→blocked)
4. UserModel, AdminUserModel (mit Status-Machine-Logik: invited→totp_pending→active→blocked)
5. JWT-Library erstellen (Token-Generierung, Validierung, pre_auth_token für Admin)
6. JwtAuthFilter (CI4 Filter für geschützte Routen)
7. AdminAuthFilter (prüft TOTP-Status: nur active-Admins zugelassen)
8. AuthController (register, login, refresh, logout, forgot-password, reset-password, verify-email)
9. AdminAuthController (login Stufe 1, totp-verify Stufe 2, totp-setup, totp-confirm, totp-recovery)
10. UserController (profile CRUD)
11. Migration: audit_log-Tabelle
12. AuditService
13. Rate-Limit-Filter
14. TOTP-Library (Secret-Generierung, Code-Validierung, Recovery-Code-Generierung)
15. CLI-Command: `php spark admin:create-super` – Erstellt initialen super_admin (nur wenn 0 Admins existieren). Interaktiv: E-Mail + Passwort eingeben → Status direkt auf `totp_pending`. Erster Login erzwingt TOTP-Setup.

### Phase 3: Subscriptions & Payment Methods
**Abhängigkeiten**: Phase 2
**Parallel möglich mit**: Phase 4

1. Migration: subscription_plans + subscription_plan_versions
2. Migration: user_subscriptions
3. Migration: payment_fee_models
4. Migration: payment_methods
5. SubscriptionPlanModel, UserSubscriptionModel
6. PaymentFeeModelModel, PaymentMethodModel
7. SubscriptionService (create, cancel, check-status, expire-cron)
8. SubscriptionController
9. PaymentMethodController
10. Cron-Command: subscription:check-expirations

### Phase 4: Provider Layer
**Abhängigkeiten**: Phase 1
**Parallel möglich mit**: Phase 3

1. Migration: providers-Tabelle
2. Migration: charge_points-Tabelle (mit Spatial Index)
3. Migration: connectors-Tabelle (mit structured_tariff_json)
4. ProviderAdapterInterface definieren (getTariff liefert StructuredTariff)
5. StructuredTariff Value Object (TariffElement, PriceComponent, TariffRestrictions)
6. ResolvedTariff Value Object
7. TariffResolver Service (resolve StructuredTariff → ResolvedTariff anhand DateTime)
8. ProviderCapabilities Value Object
9. ProviderFactory
10. MockProvider implementieren (mit JSON-Fixtures inkl. zeitabhängiger Tarife)
11. ProviderModel, ChargePointModel, ConnectorModel
12. ProviderSyncService
13. CLI-Command: provider:sync
14. ChargePointController (Suche, Details)

### Phase 5: Pricing Engine
**Abhängigkeiten**: Phase 3 + Phase 4

1. Migration: pricing_snapshots-Tabelle (erweitert: structured_tariff_json, resolved_tariff_json, tariff_type, payment_method_id, Schätzwerte)
2. Migration: system_config-Tabelle + Seed mit Default-Werten
3. PricingSnapshotModel
4. PricingEngine Service:
   - calculateEstimate(Connector, PaymentMethod, ?Subscription, DateTime) → PricingResult
   - createSnapshot() → PricingSnapshot (immutabel, inkl. StructuredTariff + ResolvedTariff)
   - calculateFinal(Snapshot, actuals) → Endbetrag in Cent (Payment-Fee zuletzt auf Gesamtbetrag)
   - calculateTransparency() → strukturierte Prozentanteile
5. PricingController (POST /charging/calculate-price → PricePreview-Response mit Komponenten, Schätzungen, Tarif-Hinweisen)

### Phase 6: Charging Sessions
**Abhängigkeiten**: Phase 5

1. Migration: charging_sessions-Tabelle (inkl. blocking_duration_seconds)
2. ChargingSessionModel
3. ChargingService:
   - startSession(): Session erstellen → Snapshot → Provider.startSession()
   - stopSession(): Provider.stopSession() → Session updaten → Billing triggern
   - getStatus(): Provider-Status → lokal updaten
4. ChargingController (start, stop, active, sessions, session-detail)
5. Cron-Command: charging:check-stale-sessions

### Phase 6a: Push-Notifications & Live-Kosten
**Abhängigkeiten**: Phase 6 + Phase 2 (user_devices benötigt users)

1. Migration: user_devices-Tabelle
2. Migration: notification_preferences-Tabelle
3. Migration: session_events-Tabelle
4. UserDeviceModel, NotificationPreferenceModel, SessionEventModel
5. FCM-Client (CI4 CURLRequest → FCM HTTP v1, OAuth2 Service-Account)
6. NotificationService (Event-Dispatch, Präferenzprüfung, Multi-Device-Versand, Token-Invalidierung)
7. LiveCostService (Snapshot + aktuelle Session-Daten → live_estimate berechnen)
8. DeviceController (register, update, delete, list)
9. NotificationPreferenceController (get, update)
10. LiveStatusController (GET /charging/active/live-status)
11. ChargingService erweitern: Session-Events schreiben, NotificationService aufrufen
12. Cron-Command: `php spark devices:cleanup` (inaktive Geräte > 90 Tage löschen)
13. Cron-Command: `php spark notifications:retry-pending` (fehlgeschlagene Pushes)
14. Blocking-Fee-Timer: `php spark charging:check-blocking-warnings` (Blocking-Warnung 5 Min vorher)

### Phase 7: Billing & Lexware
**Abhängigkeiten**: Phase 6

1. Migration: invoices-Tabelle
2. InvoiceModel
3. LexwareAdapterInterface definieren
4. MockLexwareAdapter implementieren
5. LexwareAdapter implementieren (REST-Client)
6. BillingService:
   - createInvoiceForSession() → Invoice
   - syncWithLexware() → bool
   - downloadPdf() → stored path
7. InvoiceController (list, detail, pdf-download)
8. Cron-Command: billing:retry-pending
9. Cron-Command: billing:subscription-invoices
10. writable/invoices/ Verzeichnis

### Phase 8: Admin System
**Abhängigkeiten**: Phase 7

1. AdminAuthController (Zweistufiger Login: Passwort → TOTP, Setup-Flow, Recovery)
2. AdminDashboardController (Aggregierte Daten)
3. AdminUserController (List, Detail, Block)
4. AdminProviderController (CRUD, manueller Sync)
5. AdminSessionController (List, Detail)
6. AdminInvoiceController (List, Retry)
7. AdminConfigController (System-Config CRUD)
8. AdminAuditLogController (List, Filter)
9. AdminAdminController (Einladung, Liste, Block – nur super_admin)

### Phase 9: Flutter App
**Abhängigkeiten**: Phase 6 (API muss stabil sein)
**Parallel möglich mit**: Phase 7, Phase 8

1. Flutter-Projekt initialisieren
2. Core-Layer: API-Client (Dio), Auth-Provider, Token-Storage, Routing
3. Auth-Feature: Login, Register, Forgot-Password
4. Home-Feature: Map-View mit Charge-Points, Suche, Filter
5. Charging-Feature: Preis-Vorschau (Transparenz), Session-Start/Stop, Live-Status
6. **Live-Session-Feature**: Live-Kosten-View, Foreground Service (Android), Polling-Timer, Push-Empfang
7. **Push-Setup**: firebase_messaging Integration, Token-Registrierung, Präferenz-Screen
8. Profile-Feature: Profil, Zahlungsarten, Benachrichtigungseinstellungen
9. Subscription-Feature: Plan-Übersicht, Abo-Abschluss, Kündigung
10. Invoices-Feature: Rechnungsliste, PDF-Viewer
11. Offline-Caching: Hive-Setup

### Phase 10: CI/CD & Deployment
**Inkrementell ab Phase 1**

1. Jenkinsfile für Backend erstellen
2. Health-Endpunkt für Smoke-Tests
3. Flutter-Build für Android auf Jenkins
4. Mac Mini als Jenkins Agent (Dokumentation)
5. Flutter-Build für iOS
6. .gitignore vervollständigen
7. Server-Konfiguration: Cron-Jobs einrichten

---

## 14. ENTSCHEIDUNGEN & BEGRÜNDUNGEN

| # | Entscheidung | Begründung | Alternative verworfen |
|---|-------------|------------|----------------------|
| E1 | JWT statt Session-Auth | Stateless API, ideal für Mobile-App, skalierbar | PHP-Sessions – nicht mobile-freundlich |
| E2 | Riverpod statt BLoC | Weniger Boilerplate, type-safe, bessere Async-Unterstützung | BLoC – mehr Ceremony |
| E3 | OpenStreetMap statt Google Maps | Kostenlos, kein Google-Account, DSGVO-freundlicher | Google Maps – Kosten + Datenschutz |
| E4 | Separate Admin-User-Tabelle | Keine Vermischung, unterschiedliche Auth-Flows, einfachere DSGVO-Löschung | Rollen-Feld in users |
| E5 | DB-Queue statt Redis (initial) | Keine Extra-Infrastruktur, für initiale Last ausreichend | Redis – verschoben auf Skalierung |
| E6 | Pricing-Snapshot eigene Tabelle | Strukturiert, abfragbar, validierbar, immutabel | JSON in sessions – nicht abfragbar |
| E7 | Provider-Config in DB (verschlüsselt) | Dynamisch konfigurierbar via Admin, n Provider | .env – nicht skalierbar bei n Providern |
| E8 | Kein eigenes Payment-Processing | PCI-DSS vermeiden, Komplexität reduzieren | Stripe – kann später ergänzt werden |
| E9 | OCPI 2.2.1 als erstes Protokoll | De-facto-Standard EU, breite Provider-Unterstützung | OICP – als zweiter Adapter möglich |
| E10 | Monolith statt Microservices | Einfacher für kleine Teamgröße zu entwickeln, deployen, debuggen | Microservices – Overhead nicht gerechtfertigt |
| E11 | StructuredTariff + ResolvedTariff statt NormalizedTariff | Zwei-Schicht-Modell: Rohdaten vom Provider bleiben erhalten (Audit), aufgelöste Raten für Berechnung. Zeitabhängige/gestaffelte Tarife korrekt abbildbar | Einfacher NormalizedTariff – verliert Tarif-Komplexität |
| E12 | Payment-Fee zuletzt auf Gesamtbetrag | Entspricht realer Gebührenberechnung der Zahlungsdienstleister (% auf Transaktionsbetrag) | Payment-Fee als Zwischenschritt – mathematisch falsch |
| E13 | TOTP-Pflicht für alle Admins | Kein Admin-Zugang ohne 2FA, reduziert Angriffsfläche erheblich. Status-Machine erzwingt Setup | Optional TOTP – Sicherheitslücke bei nicht-aktivierten Admins |
| E14 | TariffResolver als eigener Service | Trennung von Tarif-Auflösung (Provider-spezifisch) und Preiskalkulation (Business-Logik). Testbar, erweiterbar | Auflösung in PricingEngine – vermischt Verantwortlichkeiten |
| E15 | Snapshot-Tarif gilt für gesamte Session (MVP) | Vereinfacht Billing erheblich: ein Snapshot, ein Tarif, eine Berechnung. Zeitabhängige Tarifwechsel während laufender Session werden NICHT berücksichtigt. User wird VOR Start über zeitabhängigen Tarif informiert (PricePreview-Hinweis) | Pro-rata Berechnung bei Tarifwechsel – Komplexität für MVP nicht gerechtfertigt |
| E16 | FCM HTTP v1 (nicht Legacy) | Zukunftssicher, OAuth2-basiert, Legacy-API wird von Google abgekündigt | FCM Legacy API – deprecation angekündigt |
| E17 | Push ist best-effort, kein geschäftskritischer Kanal | Push kann fehlschlagen (Token ungültig, User hat Notifications deaktiviert). Session-Logik und Billing dürfen niemals von Push abhängen. Max. 3 Retries. | Garantierte Zustellung – nicht realistisch bei mobilen Push-Systemen |
| E18 | Polling statt WebSocket für Live-Status (MVP) | Einfacher zu implementieren, keine zusätzliche Infrastruktur, 30-Sek-Intervall ausreichend für Transparenz | WebSocket – höhere Komplexität, Post-MVP evaluieren wenn sub-5s Updates nötig |
| E19 | Euro-Schwellen für Live-Kosten-Push statt festes Intervall | Reduziert Push-Spam, relevanter für Nutzer ("Du hast gerade 5€ überschritten"), ergänzt durch 10-Min-Intervall | Festes Intervall (z.B. jede Minute) – zu viele irrelevante Pushes |
| E20 | Drei-Begriffe-Modell (preview_estimate / live_estimate / final_total) | Klare semantische Trennung verhindert Verwirrung. Jeder Begriff hat definierte Phase, Berechnung und Verbindlichkeit | Einheitlicher "Preis"-Begriff – führt zu Beschwerden wenn Estimate ≠ Final |

---

## Verzeichnis-Konventionen (Backend)

```
webserver/app/
├── Controllers/Api/V1/          # API Controller
│   ├── AuthController.php
│   ├── UserController.php
│   ├── SubscriptionController.php
│   ├── ChargingController.php
│   ├── ChargePointController.php
│   ├── InvoiceController.php
│   ├── DeviceController.php             # Push-Geräte-Registrierung
│   ├── NotificationPreferenceController.php
│   ├── LiveStatusController.php          # GET /charging/active/live-status
│   └── Admin/                   # Admin API Controller
│       ├── AdminAuthController.php  # Zweistufiger Login + TOTP-Setup
│       ├── AdminDashboardController.php
│       ├── AdminUserController.php
│       ├── AdminProviderController.php
│       ├── AdminSessionController.php
│       ├── AdminInvoiceController.php
│       ├── AdminConfigController.php
│       ├── AdminAuditLogController.php
│       └── AdminAdminController.php    # Admin-Einladung, -Liste, -Block (super_admin)
├── Models/
│   ├── UserModel.php
│   ├── AdminUserModel.php
│   ├── SubscriptionPlanModel.php
│   ├── UserSubscriptionModel.php
│   ├── PaymentMethodModel.php
│   ├── PaymentFeeModelModel.php
│   ├── ProviderModel.php
│   ├── ChargePointModel.php
│   ├── ConnectorModel.php
│   ├── ChargingSessionModel.php
│   ├── PricingSnapshotModel.php
│   ├── InvoiceModel.php
│   ├── UserDeviceModel.php
│   ├── NotificationPreferenceModel.php
│   ├── SessionEventModel.php
│   └── AuditLogModel.php
├── Entities/
│   ├── StructuredTariff.php       # Rohdaten-Tarif vom Provider (TariffElement[], PriceComponent[], TariffRestrictions)
│   ├── ResolvedTariff.php         # Zum Zeitpunkt aufgelöster Tarif (einfache Raten)
│   ├── PricingResult.php
│   └── ProviderCapabilities.php
├── Libraries/
│   ├── Auth/
│   │   ├── JwtManager.php
│   │   └── TotpManager.php        # TOTP Secret-Generierung, Code-Validierung, Recovery-Codes
│   ├── Providers/
│   │   ├── Contracts/
│   │   │   └── ProviderAdapterInterface.php
│   │   ├── Adapters/
│   │   │   ├── MockProvider.php
│   │   │   └── OcpiProvider.php
│   │   ├── ProviderFactory.php
│   │   ├── ProviderSyncService.php
│   │   └── TariffResolver.php     # StructuredTariff → ResolvedTariff Auflösung
│   ├── Pricing/
│   │   └── PricingEngine.php      # Nutzt TariffResolver, berechnet Payment-Fee zuletzt
│   ├── Billing/
│   │   ├── Contracts/
│   │   │   └── LexwareAdapterInterface.php
│   │   ├── Adapters/
│   │   │   ├── LexwareAdapter.php
│   │   │   └── MockLexwareAdapter.php
│   │   └── BillingService.php
│   └── Services/
│       ├── AuthService.php
│       ├── UserService.php
│       ├── SubscriptionService.php
│       ├── ChargingService.php
│       ├── NotificationService.php    # Event-Dispatch, FCM-Client, Token-Mgmt
│       ├── LiveCostService.php        # Snapshot + Session-Daten → live_estimate
│       └── AuditService.php
├── Filters/
│   ├── JwtAuthFilter.php
│   ├── AdminAuthFilter.php
│   └── RateLimitFilter.php
├── Database/
│   ├── Migrations/
│   │   ├── 001_CreateUsersTable.php
│   │   ├── 002_CreateUserRefreshTokensTable.php
│   │   ├── 003_CreateAdminUsersTable.php
│   │   ├── 004_CreateAuditLogTable.php
│   │   ├── 005_CreateSubscriptionPlansTable.php
│   │   ├── 006_CreateSubscriptionPlanVersionsTable.php
│   │   ├── 007_CreateUserSubscriptionsTable.php
│   │   ├── 008_CreatePaymentFeeModelsTable.php
│   │   ├── 009_CreatePaymentMethodsTable.php
│   │   ├── 010_CreateProvidersTable.php
│   │   ├── 011_CreateChargePointsTable.php
│   │   ├── 012_CreateConnectorsTable.php
│   │   ├── 013_CreateSystemConfigTable.php
│   │   ├── 014_CreatePricingSnapshotsTable.php
│   │   ├── 015_CreateChargingSessionsTable.php
│   │   ├── 016_CreateInvoicesTable.php
│   │   ├── 017_CreateUserDevicesTable.php
│   │   ├── 018_CreateNotificationPreferencesTable.php
│   │   └── 019_CreateSessionEventsTable.php
│   └── Seeds/
│       ├── AdminSeeder.php
│       ├── SubscriptionPlanSeeder.php
│       ├── PaymentFeeModelSeeder.php
│       ├── SystemConfigSeeder.php
│       └── MockProviderSeeder.php
├── Commands/
│   ├── ProviderSync.php
│   ├── BillingRetryPending.php
│   ├── BillingSubscriptionInvoices.php
│   ├── ChargingCheckStaleSessions.php
│   ├── SubscriptionCheckExpirations.php
│   ├── DeviceCleanup.php                  # Inaktive Geräte > 90 Tage löschen
│   ├── NotificationRetryPending.php        # Fehlgeschlagene Pushes wiederholen
│   ├── ChargingCheckBlockingWarnings.php   # Blocking-Fee Vorwarnung
│   └── UserHardDelete.php
└── Helpers/
```
