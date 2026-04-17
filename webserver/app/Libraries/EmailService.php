<?php

namespace App\Libraries;

use Config\Email as EmailConfig;

class EmailService
{
    protected $email;
    protected string $globalBCC;
    protected EmailConfig $config;

    public function __construct()
    {
        $this->email  = \Config\Services::email();
        $this->config = new EmailConfig();
        $this->globalBCC = $this->config->globalBCC ?? '';
    }

    /**
     * Send a templated email. Automatically adds global BCC.
     */
    public function send(string $to, string $subject, string $body, ?int $userId = null, string $template = 'generic'): bool
    {
        $this->email->clear(true);
        $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
        $this->email->setTo($to);

        if ($this->globalBCC !== '') {
            $this->email->setBCC($this->globalBCC);
        }

        $this->email->setSubject($subject);
        $this->email->setMessage($this->wrapInLayout($subject, $body));

        $success = $this->email->send(false);

        // Log to DB
        $this->logEmail($to, $template, $subject, $success, $userId);

        if (! $success) {
            log_message('error', 'Email send failed to ' . $to . ': ' . $this->email->printDebugger(['headers']));
        }

        return $success;
    }

    /**
     * Send welcome email after registration.
     */
    public function sendWelcome(string $email, string $firstName, int $userId): bool
    {
        $name = $firstName ?: 'Kunde';
        $body = <<<HTML
        <h2>Willkommen bei Transparent Laden, {$name}!</h2>
        <p>Vielen Dank für Ihre Registrierung. Um unseren Ladedienst nutzen zu können, müssen Sie noch folgende Schritte abschließen:</p>
        <ol>
            <li><strong>Nutzungsvertrag akzeptieren</strong> — Unsere AGB und Preisliste einsehen und bestätigen</li>
            <li><strong>Widerrufsverzicht erklären</strong> — Damit Sie sofort laden können (§ 356 Abs. 4 BGB)</li>
            <li><strong>Zahlungsmethode hinterlegen</strong></li>
        </ol>
        <p>Diese Schritte können Sie direkt in der App unter <strong>Profil → Vertrag</strong> erledigen.</p>
        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        HTML;

        return $this->send($email, 'Willkommen bei Transparent Laden', $body, $userId, 'welcome');
    }

    /**
     * Send contract acceptance confirmation.
     */
    public function sendContractConfirmation(string $email, string $firstName, string $version, int $userId): bool
    {
        $name = $firstName ?: 'Kunde';
        $date = date('d.m.Y H:i');
        $body = <<<HTML
        <h2>Vertragsbestätigung</h2>
        <p>Sehr geehrte/r {$name},</p>
        <p>hiermit bestätigen wir, dass Sie am <strong>{$date} Uhr</strong> die Allgemeinen Geschäftsbedingungen
        (Version {$version}) der Transparent Laden GmbH akzeptiert haben.</p>
        <h3>Vertragsgegenstand</h3>
        <p>Nutzung des Transparent-Laden-Service zum öffentlichen Laden von Elektrofahrzeugen. Die Abrechnung erfolgt
        nach den zum Ladezeitpunkt gültigen Tarifen, die in der App transparent angezeigt werden.</p>
        <h3>Ihre Rechte</h3>
        <ul>
            <li>14-tägiges Widerrufsrecht ab Vertragsschluss (§ 355 BGB)</li>
            <li>Jederzeitiges Kündigungsrecht Ihres Accounts</li>
            <li>Einsicht in alle Ladevorgänge und Rechnungen in der App</li>
        </ul>
        <p>Eine Kopie der AGB finden Sie jederzeit in der App unter <strong>Profil → Vertrag</strong>.</p>
        HTML;

        return $this->send($email, 'Ihre Vertragsbestätigung — Transparent Laden', $body, $userId, 'contract_accepted');
    }

    /**
     * Send withdrawal waiver confirmation (Widerrufsverzicht).
     */
    public function sendWithdrawalWaiverConfirmation(string $email, string $firstName, int $userId): bool
    {
        $name = $firstName ?: 'Kunde';
        $date = date('d.m.Y H:i');
        $body = <<<HTML
        <h2>Bestätigung Ihres Widerrufsverzichts</h2>
        <p>Sehr geehrte/r {$name},</p>
        <p>hiermit bestätigen wir, dass Sie am <strong>{$date} Uhr</strong> ausdrücklich verlangt haben,
        dass wir mit der Dienstleistung vor Ablauf der Widerrufsfrist beginnen.</p>
        <h3>Widerrufsbelehrung</h3>
        <p>Sie hatten das Recht, binnen 14 Tagen ohne Angabe von Gründen den Vertrag zu widerrufen.
        Die Widerrufsfrist betrug 14 Tage ab dem Tag des Vertragsschlusses.</p>
        <p>Gemäß <strong>§ 356 Abs. 4 BGB</strong> erlischt Ihr Widerrufsrecht bei einem Vertrag über die
        Erbringung von Dienstleistungen, wenn der Unternehmer die Dienstleistung vollständig erbracht hat
        und mit der Ausführung der Dienstleistung erst begonnen hat, nachdem der Verbraucher dazu seine
        ausdrückliche Zustimmung gegeben hat.</p>
        <p><strong>Hinweis:</strong> Für noch nicht erbrachte Dienstleistungen (zukünftige Ladevorgänge)
        behalten Sie selbstverständlich Ihre Verbraucherrechte. Sie können Ihren Account jederzeit kündigen.</p>
        <p>Ihre IP-Adresse und der Zeitpunkt der Zustimmung wurden zu Nachweiszwecken protokolliert.</p>
        HTML;

        return $this->send($email, 'Bestätigung Widerrufsverzicht — Transparent Laden', $body, $userId, 'withdrawal_waiver');
    }

    /**
     * Send charging session receipt.
     */
    public function sendChargingReceipt(string $email, string $firstName, array $session, int $userId): bool
    {
        $name = $firstName ?: 'Kunde';
        $kwh = number_format((float)($session['energy_kwh'] ?? 0), 2, ',', '.');
        $cost = number_format((float)($session['total_cost'] ?? 0), 2, ',', '.');
        $duration = $session['duration_minutes'] ?? '—';
        $date = isset($session['started_at']) ? date('d.m.Y H:i', strtotime($session['started_at'])) : date('d.m.Y H:i');
        $location = $session['location_name'] ?? 'Ladestation';

        $body = <<<HTML
        <h2>Ladebeleg</h2>
        <p>Hallo {$name},</p>
        <p>Ihr Ladevorgang wurde abgeschlossen. Hier die Zusammenfassung:</p>
        <table style="border-collapse:collapse;width:100%;max-width:500px;">
            <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Standort</td><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">{$location}</td></tr>
            <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Datum</td><td style="padding:8px;border-bottom:1px solid #eee;">{$date}</td></tr>
            <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Dauer</td><td style="padding:8px;border-bottom:1px solid #eee;">{$duration} Min.</td></tr>
            <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Energie</td><td style="padding:8px;border-bottom:1px solid #eee;">{$kwh} kWh</td></tr>
            <tr><td style="padding:8px;color:#666;">Gesamtkosten</td><td style="padding:8px;font-weight:bold;font-size:1.2em;">{$cost} €</td></tr>
        </table>
        <p style="margin-top:16px;">Die detaillierte Rechnung finden Sie in der App unter <strong>Profil → Rechnungen</strong>.</p>
        HTML;

        return $this->send($email, 'Ladebeleg — ' . $kwh . ' kWh für ' . $cost . ' €', $body, $userId, 'charging_receipt');
    }

    /**
     * Send password change confirmation.
     */
    public function sendPasswordChanged(string $email, string $firstName, int $userId): bool
    {
        $name = $firstName ?: 'Kunde';
        $date = date('d.m.Y H:i');
        $body = <<<HTML
        <h2>Passwort geändert</h2>
        <p>Hallo {$name},</p>
        <p>Ihr Passwort wurde am <strong>{$date} Uhr</strong> erfolgreich geändert.</p>
        <p>Falls Sie diese Änderung nicht vorgenommen haben, kontaktieren Sie uns bitte umgehend unter
        <a href="mailto:support@transparent-laden.de">support@transparent-laden.de</a>.</p>
        HTML;

        return $this->send($email, 'Passwort geändert — Transparent Laden', $body, $userId, 'password_changed');
    }

    /**
     * Wrap email body in a branded HTML layout.
     */
    private function wrapInLayout(string $title, string $content): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="de">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>{$title}</title></head>
        <body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
        <div style="max-width:600px;margin:0 auto;padding:20px;">
            <div style="text-align:center;padding:24px 0;">
                <span style="font-size:28px;">⚡</span>
                <h1 style="margin:8px 0 0;font-size:22px;color:#1a1a1a;">Transparent Laden</h1>
            </div>
            <div style="background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                {$content}
            </div>
            <div style="text-align:center;padding:24px 0;color:#9ca3af;font-size:12px;">
                <p>Transparent Laden GmbH · München · Deutschland</p>
                <p><a href="https://transparent-laden.de/datenschutz" style="color:#9ca3af;">Datenschutz</a> ·
                   <a href="https://transparent-laden.de/impressum" style="color:#9ca3af;">Impressum</a></p>
                <p>Diese E-Mail wurde automatisch versendet. Bitte antworten Sie nicht darauf.</p>
            </div>
        </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Log email to database.
     */
    private function logEmail(string $to, string $template, string $subject, bool $success, ?int $userId): void
    {
        try {
            $db = \Config\Database::connect();
            $db->table('email_log')->insert([
                'user_id'       => $userId,
                'to_email'      => $to,
                'template'      => $template,
                'subject'       => $subject,
                'status'        => $success ? 'sent' : 'failed',
                'error_message' => $success ? null : 'Check CI log for details',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Email log insert failed: ' . $e->getMessage());
        }
    }
}
