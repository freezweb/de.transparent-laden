<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Payment extends BaseConfig
{
    // ── Stripe ──────────────────────────────────────────────
    public string $stripeSecretKey      = '';
    public string $stripePublishableKey = '';
    public string $stripeWebhookSecret  = '';
    public string $stripeCurrency       = 'eur';

    // Pre-Auth: Maximalbetrag in Cent für eine Ladevorgang-Vorautorisierung
    public int $preAuthAmountCent = 5000; // 50 €

    // ── PayPal ──────────────────────────────────────────────
    public string $paypalClientId     = '';
    public string $paypalClientSecret = '';
    public string $paypalMode         = 'sandbox'; // 'sandbox' or 'live'

    public function __construct()
    {
        parent::__construct();

        // Lade aus .env
        $this->stripeSecretKey      = env('payment.stripeSecretKey', $this->stripeSecretKey);
        $this->stripePublishableKey = env('payment.stripePublishableKey', $this->stripePublishableKey);
        $this->stripeWebhookSecret  = env('payment.stripeWebhookSecret', $this->stripeWebhookSecret);
        $this->stripeCurrency       = env('payment.stripeCurrency', $this->stripeCurrency);
        $this->preAuthAmountCent    = (int) env('payment.preAuthAmountCent', $this->preAuthAmountCent);
        $this->paypalClientId       = env('payment.paypalClientId', $this->paypalClientId);
        $this->paypalClientSecret   = env('payment.paypalClientSecret', $this->paypalClientSecret);
        $this->paypalMode           = env('payment.paypalMode', $this->paypalMode);
    }

    public function getPayPalBaseUrl(): string
    {
        return $this->paypalMode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
