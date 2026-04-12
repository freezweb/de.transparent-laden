<?php

namespace App\Libraries;

use Config\Payment;

class StripeService
{
    private Payment $config;

    public function __construct()
    {
        $this->config = config(Payment::class);
        \Stripe\Stripe::setApiKey($this->config->stripeSecretKey);
    }

    /**
     * Stripe-Customer für einen User anlegen oder vorhandenen zurückgeben.
     */
    public function getOrCreateCustomer(int $userId, string $email): string
    {
        $userModel = model(\App\Models\UserModel::class);
        $user = $userModel->find($userId);

        if (! empty($user['stripe_customer_id'])) {
            return $user['stripe_customer_id'];
        }

        $customer = \Stripe\Customer::create([
            'email'    => $email,
            'metadata' => ['user_id' => $userId],
        ]);

        $userModel->update($userId, ['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * SetupIntent erstellen — Client nutzt das clientSecret,
     * um Kartendaten sicher an Stripe zu senden.
     */
    public function createSetupIntent(string $customerId, string $type = 'card'): array
    {
        $paymentMethodTypes = match ($type) {
            'sepa'    => ['sepa_debit'],
            default   => ['card'],
        };

        $intent = \Stripe\SetupIntent::create([
            'customer'             => $customerId,
            'payment_method_types' => $paymentMethodTypes,
        ]);

        return [
            'setup_intent_id' => $intent->id,
            'client_secret'   => $intent->client_secret,
        ];
    }

    /**
     * Payment-Method-Details von Stripe abrufen.
     */
    public function getPaymentMethod(string $stripePaymentMethodId): \Stripe\PaymentMethod
    {
        return \Stripe\PaymentMethod::retrieve($stripePaymentMethodId);
    }

    /**
     * Payment-Method an Customer attachen.
     */
    public function attachPaymentMethod(string $customerId, string $paymentMethodId): \Stripe\PaymentMethod
    {
        $pm = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $pm->attach(['customer' => $customerId]);
        return $pm;
    }

    /**
     * Pre-Authorization: PaymentIntent mit manual capture erstellen.
     * Blockiert den Betrag auf der Karte, bucht aber noch nicht ab.
     */
    public function preAuthorize(string $customerId, string $paymentMethodId, int $amountCent, string $description = ''): array
    {
        $intent = \Stripe\PaymentIntent::create([
            'amount'               => $amountCent,
            'currency'             => $this->config->stripeCurrency,
            'customer'             => $customerId,
            'payment_method'       => $paymentMethodId,
            'capture_method'       => 'manual',
            'confirm'              => true,
            'description'          => $description,
            'off_session'          => true,
            'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
        ]);

        return [
            'payment_intent_id' => $intent->id,
            'status'            => $intent->status,
            'amount'            => $intent->amount,
        ];
    }

    /**
     * Capture: Endbetrag abbuchen (muss <= Pre-Auth-Betrag sein).
     */
    public function capture(string $paymentIntentId, int $finalAmountCent): array
    {
        $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
        $captured = $intent->capture([
            'amount_to_capture' => $finalAmountCent,
        ]);

        return [
            'payment_intent_id' => $captured->id,
            'status'            => $captured->status,
            'amount_captured'   => $captured->amount_received,
        ];
    }

    /**
     * Pre-Auth stornieren (z.B. wenn Ladevorgang abgebrochen wird).
     */
    public function cancelIntent(string $paymentIntentId): bool
    {
        $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
        $intent->cancel();
        return true;
    }

    /**
     * Payment-Method von Stripe abtrennen (löschen).
     */
    public function detachPaymentMethod(string $paymentMethodId): bool
    {
        $pm = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $pm->detach();
        return true;
    }

    /**
     * Publishable Key für Client zurückgeben.
     */
    public function getPublishableKey(): string
    {
        return $this->config->stripePublishableKey;
    }
}
