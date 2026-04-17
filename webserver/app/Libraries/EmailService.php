<?php

namespace App\Libraries;

use Config\Email as EmailConfig;

class EmailService
{
    protected $email;
    protected string $globalBCC;

    public function __construct()
    {
        $this->email = \Config\Services::email();
        $config = new EmailConfig();
        $this->globalBCC = $config->globalBCC ?? '';
    }

    /**
     * Send an HTML email. Automatically adds global BCC if configured.
     */
    public function send(string $to, string $subject, string $body, ?string $fromName = null): bool
    {
        $this->email->clear(true);

        $config = new EmailConfig();
        $this->email->setFrom($config->fromEmail, $fromName ?? $config->fromName);
        $this->email->setTo($to);

        if ($this->globalBCC !== '') {
            $this->email->setBCC($this->globalBCC);
        }

        $this->email->setSubject($subject);
        $this->email->setMessage($body);

        if (! $this->email->send()) {
            log_message('error', 'Email send failed: ' . $this->email->printDebugger(['headers']));
            return false;
        }

        return true;
    }

    /**
     * Access the underlying CI Email instance for advanced usage.
     * Global BCC is NOT automatically added — call applyBCC() afterwards.
     */
    public function raw(): \CodeIgniter\Email\Email
    {
        return $this->email;
    }

    /**
     * Apply global BCC to the underlying email instance.
     */
    public function applyBCC(): static
    {
        if ($this->globalBCC !== '') {
            $this->email->setBCC($this->globalBCC);
        }
        return $this;
    }
}
