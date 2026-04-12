<?php

namespace App\Libraries;

use App\Models\PaymentMethodModel;
use Config\Payment;

/**
 * Einheitliches Payment-Gateway — delegiert an Stripe oder PayPal
 * je nach Zahlungsart der gespeicherten PaymentMethod.
 */
class PaymentGateway
{
    private StripeService $stripe;
    private PayPalService $paypal;
    private PaymentMethodModel $methodModel;
    private Payment $config;

    public function __construct()
    {
        $this->stripe      = new StripeService();
        $this->paypal      = new PayPalService();
        $this->methodModel = model(PaymentMethodModel::class);
        $this->config      = config(Payment::class);
    }

    /**
     * Pre-Authorization für einen Ladevorgang.
     * Gibt payment_intent_id (Stripe) oder authorization_id (PayPal) zurück.
     */
    public function preAuthorize(int $userId, int $paymentMethodId, int $sessionId): array
    {
        $method = $this->methodModel->find($paymentMethodId);
        if (! $method || $method['user_id'] !== $userId || $method['status'] !== 'active') {
            throw new \RuntimeException('Ungültige Zahlungsmethode');
        }

        $amount      = $this->config->preAuthAmountCent;
        $description = "Einfach Laden – Ladevorgang #$sessionId";

        if ($method['type'] === 'paypal') {
            // PayPal: Billing-Agreement-basierte Autorisierung
            $agreementId = $method['external_reference'];
            if (empty($agreementId)) {
                throw new \RuntimeException('PayPal-Konto nicht verknüpft');
            }

            $result = $this->paypal->createAuthorization($agreementId, $amount, $description);
            return [
                'gateway'          => 'paypal',
                'authorization_id' => $result['authorization_id'],
                'status'           => $result['status'],
            ];
        }

        // Stripe: Alle anderen Typen (credit_card, debit_card, sepa, apple_pay, google_pay)
        $userModel  = model(\App\Models\UserModel::class);
        $user       = $userModel->find($userId);
        $customerId = $user['stripe_customer_id'] ?? null;

        if (empty($customerId) || empty($method['external_reference'])) {
            throw new \RuntimeException('Stripe-Zahlungsmethode nicht vollständig eingerichtet');
        }

        $result = $this->stripe->preAuthorize($customerId, $method['external_reference'], $amount, $description);

        return [
            'gateway'           => 'stripe',
            'payment_intent_id' => $result['payment_intent_id'],
            'status'            => $result['status'],
        ];
    }

    /**
     * Capture: Endbetrag abbuchen nach Ladeende.
     */
    public function capture(string $gateway, string $referenceId, int $finalAmountCent): array
    {
        if ($gateway === 'paypal') {
            return $this->paypal->captureAuthorization($referenceId, $finalAmountCent);
        }

        return $this->stripe->capture($referenceId, $finalAmountCent);
    }

    /**
     * Pre-Auth stornieren.
     */
    public function cancelAuthorization(string $gateway, string $referenceId): bool
    {
        if ($gateway === 'paypal') {
            return $this->paypal->voidAuthorization($referenceId);
        }

        return $this->stripe->cancelIntent($referenceId);
    }
}
