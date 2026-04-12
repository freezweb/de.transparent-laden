<?php

namespace App\Libraries;

use Config\Payment;

class PayPalService
{
    private Payment $config;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->config = config(Payment::class);
    }

    /**
     * OAuth2-Token von PayPal holen.
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', $this->config->getPayPalBaseUrl() . '/v1/oauth2/token', [
            'auth'        => [$this->config->paypalClientId, $this->config->paypalClientSecret],
            'form_params' => ['grant_type' => 'client_credentials'],
            'headers'     => ['Accept' => 'application/json'],
        ]);

        $data = json_decode($response->getBody(), true);
        $this->accessToken = $data['access_token'];
        return $this->accessToken;
    }

    /**
     * Billing Agreement Token erstellen — User wird zu PayPal weitergeleitet.
     */
    public function createBillingAgreementToken(string $returnUrl, string $cancelUrl): array
    {
        $token = $this->getAccessToken();
        $client = \Config\Services::curlrequest();

        $response = $client->request('POST', $this->config->getPayPalBaseUrl() . '/v1/billing-agreements/agreement-tokens', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'description' => 'Einfach Laden – Zahlungsvereinbarung',
                'payer'       => ['payment_method' => 'PAYPAL'],
                'plan'        => [
                    'type'                 => 'MERCHANT_INITIATED_BILLING',
                    'merchant_preferences' => [
                        'return_url'              => $returnUrl,
                        'cancel_url'              => $cancelUrl,
                        'accepted_pymt_type'      => 'INSTANT',
                        'skip_shipping_address'   => true,
                        'immutable_shipping_address' => false,
                    ],
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        $approvalUrl = '';
        foreach ($data['links'] ?? [] as $link) {
            if ($link['rel'] === 'approval_url') {
                $approvalUrl = $link['href'];
                break;
            }
        }

        return [
            'token_id'     => $data['token_id'],
            'approval_url' => $approvalUrl,
        ];
    }

    /**
     * Billing Agreement aus Token-Bestätigung erstellen.
     */
    public function executeBillingAgreement(string $tokenId): array
    {
        $token = $this->getAccessToken();
        $client = \Config\Services::curlrequest();

        $response = $client->request('POST', $this->config->getPayPalBaseUrl() . '/v1/billing-agreements/agreements', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'token_id' => $tokenId,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'agreement_id' => $data['id'],
            'payer_email'  => $data['payer']['payer_info']['email'] ?? null,
            'payer_id'     => $data['payer']['payer_info']['payer_id'] ?? null,
            'status'       => $data['state'] ?? $data['status'] ?? 'unknown',
        ];
    }

    /**
     * Autorisierung über Billing Agreement erstellen (Pre-Auth).
     */
    public function createAuthorization(string $agreementId, int $amountCent, string $description = ''): array
    {
        $token  = $this->getAccessToken();
        $client = \Config\Services::curlrequest();
        $amount = number_format($amountCent / 100, 2, '.', '');

        $response = $client->request('POST', $this->config->getPayPalBaseUrl() . '/v1/payments/billing-agreements/' . $agreementId . '/agreement-transactions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'intent' => 'authorize',
                'payer'  => ['payment_method' => 'PAYPAL'],
                'transactions' => [
                    [
                        'amount' => [
                            'total'    => $amount,
                            'currency' => 'EUR',
                        ],
                        'description' => $description,
                    ],
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'authorization_id' => $data['id'] ?? null,
            'status'           => $data['state'] ?? $data['status'] ?? 'unknown',
        ];
    }

    /**
     * Autorisierung capturen (Endbetrag buchen).
     */
    public function captureAuthorization(string $authorizationId, int $amountCent): array
    {
        $token  = $this->getAccessToken();
        $client = \Config\Services::curlrequest();
        $amount = number_format($amountCent / 100, 2, '.', '');

        $response = $client->request('POST', $this->config->getPayPalBaseUrl() . '/v2/payments/authorizations/' . $authorizationId . '/capture', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'amount' => [
                    'currency_code' => 'EUR',
                    'value'         => $amount,
                ],
                'final_capture' => true,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'capture_id' => $data['id'] ?? null,
            'status'     => $data['status'] ?? 'unknown',
        ];
    }

    /**
     * Autorisierung stornieren.
     */
    public function voidAuthorization(string $authorizationId): bool
    {
        $token  = $this->getAccessToken();
        $client = \Config\Services::curlrequest();

        $client->request('POST', $this->config->getPayPalBaseUrl() . '/v2/payments/authorizations/' . $authorizationId . '/void', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
        ]);

        return true;
    }
}
