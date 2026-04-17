<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Libraries\EmailService;

class ContractController extends ApiBaseController
{
    private const TERMS_VERSION = '1.0';

    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
    }

    /**
     * GET /api/v1/contract/status
     * Returns the user's contract + withdrawal status.
     */
    public function status()
    {
        $user = $this->userModel->find($this->userId);
        if (! $user) {
            return $this->failNotFound('User not found');
        }

        return $this->respond([
            'terms_accepted'       => ! empty($user['terms_accepted_at']),
            'terms_accepted_at'    => $user['terms_accepted_at'],
            'terms_version'        => $user['terms_version'],
            'current_version'      => self::TERMS_VERSION,
            'withdrawal_waived'    => ! empty($user['withdrawal_waived_at']),
            'withdrawal_waived_at' => $user['withdrawal_waived_at'],
            'can_charge'           => ! empty($user['terms_accepted_at']) && ! empty($user['withdrawal_waived_at']),
        ]);
    }

    /**
     * GET /api/v1/contract/terms
     * Returns the full terms & conditions text.
     */
    public function terms()
    {
        return $this->respond([
            'version' => self::TERMS_VERSION,
            'title'   => 'Allgemeine Geschäftsbedingungen',
            'content' => $this->getTermsContent(),
        ]);
    }

    /**
     * POST /api/v1/contract/accept
     * Accept the terms & conditions.
     */
    public function accept()
    {
        $data = $this->request->getJSON(true);
        $version = $data['version'] ?? '';

        if ($version !== self::TERMS_VERSION) {
            return $this->failValidationErrors(['version' => 'Bitte aktuelle AGB-Version akzeptieren (v' . self::TERMS_VERSION . ')']);
        }

        $user = $this->userModel->find($this->userId);
        if (! $user) {
            return $this->failNotFound();
        }

        $this->userModel->update($this->userId, [
            'terms_accepted_at' => date('Y-m-d H:i:s'),
            'terms_version'     => self::TERMS_VERSION,
        ]);

        // Send confirmation email
        $mailer = new EmailService();
        $mailer->sendContractConfirmation(
            $user['email'],
            $user['first_name'] ?? '',
            self::TERMS_VERSION,
            $this->userId
        );

        return $this->respond([
            'message'           => 'AGB akzeptiert',
            'terms_accepted_at' => date('Y-m-d H:i:s'),
            'terms_version'     => self::TERMS_VERSION,
        ]);
    }

    /**
     * POST /api/v1/contract/waive-withdrawal
     * Waive the 14-day withdrawal right to start charging immediately.
     */
    public function waiveWithdrawal()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['confirmed']) || $data['confirmed'] !== true) {
            return $this->failValidationErrors(['confirmed' => 'Sie müssen den Verzicht ausdrücklich bestätigen.']);
        }

        $user = $this->userModel->find($this->userId);
        if (! $user) {
            return $this->failNotFound();
        }

        if (empty($user['terms_accepted_at'])) {
            return $this->fail('Bitte zuerst die AGB akzeptieren.', 422);
        }

        $clientIp = $this->request->getIPAddress();

        $this->userModel->update($this->userId, [
            'withdrawal_waived_at' => date('Y-m-d H:i:s'),
            'withdrawal_waiver_ip' => $clientIp,
        ]);

        // Send confirmation email
        $mailer = new EmailService();
        $mailer->sendWithdrawalWaiverConfirmation(
            $user['email'],
            $user['first_name'] ?? '',
            $this->userId
        );

        return $this->respond([
            'message'              => 'Widerrufsverzicht erklärt. Sie können jetzt laden.',
            'withdrawal_waived_at' => date('Y-m-d H:i:s'),
            'can_charge'           => true,
        ]);
    }

    /**
     * Full German AGB text for EV charging.
     */
    private function getTermsContent(): string
    {
        return <<<'AGB'
## § 1 Geltungsbereich

(1) Diese Allgemeinen Geschäftsbedingungen (AGB) gelten für alle über die Transparent Laden App geschlossenen Verträge zwischen der Transparent Laden GmbH (nachfolgend „Anbieter") und dem registrierten Nutzer (nachfolgend „Kunde") über die Nutzung öffentlicher Ladeinfrastruktur für Elektrofahrzeuge.

(2) Abweichende Bedingungen des Kunden werden nicht anerkannt, es sei denn, der Anbieter stimmt ihrer Geltung ausdrücklich schriftlich zu.

## § 2 Vertragsschluss

(1) Der Vertrag kommt durch die Registrierung in der App und die Akzeptanz dieser AGB zustande.

(2) Der Kunde muss volljährig und geschäftsfähig sein.

(3) Für jeden einzelnen Ladevorgang kommt ein separater Dienstleistungsvertrag zustande, sobald der Kunde den Ladevorgang über die App startet.

## § 3 Leistungsbeschreibung

(1) Der Anbieter vermittelt den Zugang zu öffentlichen Ladestationen verschiedener Betreiber (Charge Point Operators, CPO) und ermöglicht die bargeldlose Abrechnung von Ladevorgängen.

(2) Die in der App angezeigten Preise sind stets die aktuellen Endpreise inkl. MwSt. Die Preisgestaltung ist vollständig transparent: Der Einkaufspreis beim CPO, die Netzentgelte sowie der Aufschlag des Anbieters werden separat ausgewiesen.

(3) Der Anbieter garantiert keine bestimmte Verfügbarkeit oder Ladeleistung der Ladestationen, da diese vom jeweiligen Betreiber abhängen.

## § 4 Preise und Zahlung

(1) Die Abrechnung erfolgt verbrauchsgenau pro kWh zu dem zum Ladezeitpunkt in der App angezeigten Tarif. Zusätzlich können zeitbasierte Kosten (Blockiergebühren) anfallen, die vor Ladebeginn angezeigt werden.

(2) Der Kunde hinterlegt eine gültige Zahlungsmethode (Kreditkarte, PayPal, SEPA-Lastschrift). Die Belastung erfolgt nach Abschluss des Ladevorgangs.

(3) Rechnungen werden elektronisch in der App bereitgestellt und per E-Mail an die hinterlegte Adresse versendet.

## § 5 Widerrufsrecht

(1) Der Kunde hat das Recht, binnen 14 Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen. Die Widerrufsfrist beträgt 14 Tage ab dem Tag des Vertragsschlusses.

(2) Um das Widerrufsrecht auszuüben, muss der Kunde den Anbieter (Transparent Laden GmbH, E-Mail: widerruf@transparent-laden.de) mittels einer eindeutigen Erklärung über seinen Entschluss informieren.

(3) **Vorzeitiges Erlöschen des Widerrufsrechts:** Gemäß § 356 Abs. 4 BGB erlischt das Widerrufsrecht bei einem Vertrag über die Erbringung von Dienstleistungen, wenn der Unternehmer die Dienstleistung vollständig erbracht hat und mit der Ausführung erst begonnen hat, nachdem der Verbraucher ausdrücklich zugestimmt und gleichzeitig seine Kenntnis davon bestätigt hat, dass er sein Widerrufsrecht bei vollständiger Vertragserfüllung verliert.

(4) Der Kunde kann in der App ausdrücklich verlangen, dass die Dienstleistung (Laden) sofort beginnt, bevor die Widerrufsfrist abgelaufen ist. In diesem Fall wird der Kunde darüber informiert, dass sein Widerrufsrecht bei vollständiger Erbringung der jeweiligen Ladedienstleistung erlischt.

## § 6 Pflichten des Kunden

(1) Der Kunde ist verpflichtet, die Ladestation sachgemäß zu bedienen und nach Beendigung des Ladevorgangs den Ladeplatz zeitnah freizugeben.

(2) Der Kunde haftet für Schäden, die durch unsachgemäße Benutzung entstehen.

(3) Die Zugangsdaten (E-Mail/Passwort) sind vertraulich zu behandeln.

## § 7 Haftung

(1) Der Anbieter haftet unbeschränkt für vorsätzlich oder grob fahrlässig verursachte Schäden.

(2) Für leichte Fahrlässigkeit haftet der Anbieter nur bei Verletzung wesentlicher Vertragspflichten (Kardinalpflichten), begrenzt auf den vorhersehbaren, vertragstypischen Schaden.

(3) Die Haftung für Schäden an Fahrzeugen durch defekte Ladestationen richtet sich nach den Bedingungen des jeweiligen Ladestationsbetreibers.

## § 8 Datenschutz

Die Erhebung und Verarbeitung personenbezogener Daten erfolgt gemäß unserer Datenschutzerklärung, einsehbar unter https://transparent-laden.de/datenschutz und in der App.

## § 9 Vertragslaufzeit und Kündigung

(1) Der Rahmenvertrag wird auf unbestimmte Zeit geschlossen und kann vom Kunden jederzeit ohne Einhaltung einer Frist gekündigt werden (Accountlöschung in der App).

(2) Der Anbieter kann den Vertrag mit einer Frist von 14 Tagen kündigen. Das Recht zur Sperrung bei Vertragsverstößen bleibt unberührt.

(3) Offene Forderungen bleiben von einer Kündigung unberührt.

## § 10 Streitbeilegung

(1) Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung bereit: https://ec.europa.eu/consumers/odr

(2) Der Anbieter ist nicht verpflichtet und nicht bereit, an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.

## § 11 Schlussbestimmungen

(1) Es gilt das Recht der Bundesrepublik Deutschland unter Ausschluss des UN-Kaufrechts.

(2) Sollte eine Bestimmung dieser AGB unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen davon unberührt.

Stand: April 2026 · Version 1.0
AGB;
    }
}
