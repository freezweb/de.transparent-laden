<?php

namespace App\Controllers\Api;

use App\Libraries\StripeService;
use App\Libraries\PayPalService;
use App\Models\PaymentMethodModel;
use App\Models\PaymentFeeModelModel;

class PaymentMethodController extends ApiBaseController
{
    private PaymentMethodModel $paymentModel;
    private PaymentFeeModelModel $feeModel;

    public function __construct()
    {
        $this->paymentModel = model(PaymentMethodModel::class);
        $this->feeModel     = model(PaymentFeeModelModel::class);
    }

    /**
     * GET /payment-methods — Alle aktiven Zahlungsmethoden des Users.
     */
    public function index()
    {
        $methods = $this->paymentModel->getForUser($this->userId);
        return $this->respond(['payment_methods' => $methods]);
    }

    /**
     * GET /payment-methods/config — Stripe Publishable Key + PayPal Client ID.
     * Wird vom Client für SDK-Initialisierung benötigt.
     */
    public function config()
    {
        $config = config(\Config\Payment::class);
        return $this->respond([
            'stripe_publishable_key' => $config->stripePublishableKey,
            'paypal_client_id'       => $config->paypalClientId,
            'paypal_mode'            => $config->paypalMode,
        ]);
    }

    /**
     * POST /payment-methods/setup-intent — Stripe SetupIntent erstellen.
     * Client nutzt client_secret um Karte/SEPA sicher zu speichern.
     *
     * Body: { "type": "credit_card|debit_card|sepa|apple_pay|google_pay" }
     */
    public function createSetupIntent()
    {
        $config = config(\Config\Payment::class);
        if (empty($config->stripeSecretKey)) {
            return $this->fail('Stripe ist noch nicht konfiguriert. Bitte kontaktiere den Support.', 503);
        }

        $data = $this->request->getJSON(true);
        $type = $data['type'] ?? 'credit_card';

        $stripeType = in_array($type, ['sepa']) ? 'sepa' : 'card';

        try {
            $stripe     = new StripeService();
            $customerId = $stripe->getOrCreateCustomer($this->userId, $this->userEmail);
            $intent     = $stripe->createSetupIntent($customerId, $stripeType);

            return $this->respond([
                'client_secret'   => $intent['client_secret'],
                'setup_intent_id' => $intent['setup_intent_id'],
                'customer_id'     => $customerId,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'SetupIntent error: ' . $e->getMessage());
            return $this->fail('Zahlungsmethode konnte nicht eingerichtet werden. Bitte versuche es später erneut.');
        }
    }

    /**
     * POST /payment-methods/confirm-stripe — Nach erfolgreicher Client-Bestätigung
     * die Stripe PaymentMethod speichern.
     *
     * Body: { "setup_intent_id": "seti_...", "type": "credit_card", "label": "Visa ****4242" }
     */
    public function confirmStripe()
    {
        $rules = [
            'setup_intent_id' => 'required',
            'type'            => 'required|in_list[credit_card,debit_card,sepa,apple_pay,google_pay]',
            'label'           => 'permit_empty|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        try {
            $stripe      = new StripeService();
            $setupIntent = \Stripe\SetupIntent::retrieve($data['setup_intent_id']);

            if ($setupIntent->status !== 'succeeded') {
                return $this->fail('SetupIntent noch nicht bestätigt (Status: ' . $setupIntent->status . ')');
            }

            $pmId     = $setupIntent->payment_method;
            $pmObject = $stripe->getPaymentMethod($pmId);

            // Label generieren wenn nicht angegeben
            $label = $data['label'] ?? null;
            if (empty($label)) {
                $label = $this->generateLabel($data['type'], $pmObject);
            }

            $feeModelRecord = $this->feeModel->getActiveForType($data['type']);

            $methodId = $this->paymentModel->insert([
                'user_id'            => $this->userId,
                'type'               => $data['type'],
                'label'              => $label,
                'external_reference' => $pmId,
                'fee_model_id'       => $feeModelRecord['id'] ?? null,
                'is_default'         => 0,
                'status'             => 'active',
            ]);

            // Erste Zahlungsmethode automatisch als Standard setzen
            $existing = $this->paymentModel->getForUser($this->userId);
            if (count($existing) === 1) {
                $this->paymentModel->setDefault($this->userId, $methodId);
            }

            return $this->respondCreated([
                'payment_method_id' => $methodId,
                'label'             => $label,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Confirm Stripe error: ' . $e->getMessage());
            return $this->fail('Zahlungsmethode konnte nicht gespeichert werden. Bitte versuche es erneut.');
        }
    }

    /**
     * POST /payment-methods/paypal/setup — PayPal Billing Agreement starten.
     * Gibt approval_url zurück — Client öffnet diese in WebView.
     *
     * Body: { "return_url": "...", "cancel_url": "..." }
     */
    public function paypalSetup()
    {
        $config = config(\Config\Payment::class);
        if (empty($config->paypalClientId) || empty($config->paypalClientSecret)) {
            return $this->fail('PayPal ist noch nicht konfiguriert. Bitte kontaktiere den Support.', 503);
        }

        $data       = $this->request->getJSON(true);
        $returnUrl  = $data['return_url'] ?? 'https://transparent-laden.de/api/v1/payment-methods/paypal/callback';
        $cancelUrl  = $data['cancel_url'] ?? 'https://transparent-laden.de/api/v1/payment-methods/paypal/cancel';

        try {
            $paypal = new PayPalService();
            $result = $paypal->createBillingAgreementToken($returnUrl, $cancelUrl);

            return $this->respond([
                'token_id'     => $result['token_id'],
                'approval_url' => $result['approval_url'],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PayPal setup error: ' . $e->getMessage());
            return $this->fail('PayPal-Einrichtung fehlgeschlagen. Bitte versuche es später erneut.');
        }
    }

    /**
     * POST /payment-methods/paypal/confirm — Nach User-Bestätigung in PayPal
     * die Billing Agreement abschließen und speichern.
     *
     * Body: { "token_id": "BA-...", "label": "optional" }
     */
    public function paypalConfirm()
    {
        $rules = ['token_id' => 'required'];
        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        try {
            $paypal = new PayPalService();
            $result = $paypal->executeBillingAgreement($data['token_id']);

            if (! in_array($result['status'], ['Active', 'active', 'ACTIVE'])) {
                return $this->fail('PayPal-Vereinbarung nicht aktiv: ' . $result['status']);
            }

            $label = $data['label'] ?? null;
            if (empty($label)) {
                $label = 'PayPal' . (! empty($result['payer_email']) ? ' (' . $result['payer_email'] . ')' : '');
            }

            $feeModelRecord = $this->feeModel->getActiveForType('paypal');

            $methodId = $this->paymentModel->insert([
                'user_id'            => $this->userId,
                'type'               => 'paypal',
                'label'              => $label,
                'external_reference' => $result['agreement_id'],
                'fee_model_id'       => $feeModelRecord['id'] ?? null,
                'is_default'         => 0,
                'status'             => 'active',
            ]);

            $existing = $this->paymentModel->getForUser($this->userId);
            if (count($existing) === 1) {
                $this->paymentModel->setDefault($this->userId, $methodId);
            }

            return $this->respondCreated([
                'payment_method_id' => $methodId,
                'label'             => $label,
                'payer_email'       => $result['payer_email'],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PayPal confirm error: ' . $e->getMessage());
            return $this->fail('PayPal-Verknüpfung fehlgeschlagen. Bitte versuche es erneut.');
        }
    }

    // ── Bestehende Endpunkte ──

    public function show(int $id)
    {
        $method = $this->paymentModel->find($id);
        if (! $method || $method['user_id'] !== $this->userId) {
            return $this->failNotFound('Zahlungsmethode nicht gefunden');
        }

        return $this->respond(['payment_method' => $method]);
    }

    public function setDefault(int $id)
    {
        $method = $this->paymentModel->find($id);
        if (! $method || $method['user_id'] !== $this->userId) {
            return $this->failNotFound('Zahlungsmethode nicht gefunden');
        }

        $this->paymentModel->setDefault($this->userId, $id);
        return $this->respond(['message' => 'Standard-Zahlungsmethode aktualisiert']);
    }

    public function delete(int $id)
    {
        $method = $this->paymentModel->find($id);
        if (! $method || $method['user_id'] !== $this->userId) {
            return $this->failNotFound('Zahlungsmethode nicht gefunden');
        }

        if ($method['is_default']) {
            return $this->fail('Standard-Zahlungsmethode kann nicht gelöscht werden. Setze zuerst eine andere als Standard.', 409);
        }

        // Stripe PaymentMethod detachen
        if ($method['type'] !== 'paypal' && ! empty($method['external_reference'])) {
            try {
                $stripe = new StripeService();
                $stripe->detachPaymentMethod($method['external_reference']);
            } catch (\Exception $e) {
                log_message('warning', 'Stripe detach failed for PM ' . $id . ': ' . $e->getMessage());
            }
        }

        $this->paymentModel->update($id, ['status' => 'inactive']);
        return $this->respondDeleted(['message' => 'Zahlungsmethode entfernt']);
    }

    // ── Hilfsmethoden ──

    private function generateLabel(string $type, \Stripe\PaymentMethod $pm): string
    {
        return match ($type) {
            'credit_card', 'debit_card' => ucfirst($pm->card->brand ?? 'Karte') . ' ****' . ($pm->card->last4 ?? '????'),
            'sepa'       => 'SEPA ****' . ($pm->sepa_debit->last4 ?? '????'),
            'apple_pay'  => 'Apple Pay' . (! empty($pm->card->last4) ? ' ****' . $pm->card->last4 : ''),
            'google_pay' => 'Google Pay' . (! empty($pm->card->last4) ? ' ****' . $pm->card->last4 : ''),
            default      => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
