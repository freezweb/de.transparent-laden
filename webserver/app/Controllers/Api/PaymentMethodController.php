<?php

namespace App\Controllers\Api;

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

    public function index()
    {
        $methods = $this->paymentModel->getForUser($this->userId);
        return $this->respond(['payment_methods' => $methods]);
    }

    public function store()
    {
        $rules = [
            'type'            => 'required|in_list[credit_card,debit_card,paypal,sepa,apple_pay,google_pay]',
            'token_reference' => 'required|max_length[500]',
            'display_name'    => 'permit_empty|max_length[100]',
            'is_default'      => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        $feeModelRecord = $this->feeModel->getActiveForType($data['type']);

        $methodId = $this->paymentModel->insert([
            'user_id'         => $this->userId,
            'type'            => $data['type'],
            'token_reference' => $data['token_reference'],
            'display_name'    => $data['display_name'] ?? null,
            'fee_model_id'    => $feeModelRecord['id'] ?? null,
            'is_default'      => 0,
            'status'          => 'active',
        ]);

        if (! empty($data['is_default'])) {
            $this->paymentModel->setDefault($this->userId, $methodId);
        }

        $existing = $this->paymentModel->getForUser($this->userId);
        if (count($existing) === 1) {
            $this->paymentModel->setDefault($this->userId, $methodId);
        }

        return $this->respondCreated(['payment_method_id' => $methodId]);
    }

    public function show(int $id)
    {
        $method = $this->paymentModel->find($id);
        if (! $method || $method['user_id'] !== $this->userId) {
            return $this->failNotFound('Payment method not found');
        }

        return $this->respond(['payment_method' => $method]);
    }

    public function setDefault(int $id)
    {
        $method = $this->paymentModel->find($id);
        if (! $method || $method['user_id'] !== $this->userId) {
            return $this->failNotFound('Payment method not found');
        }

        $this->paymentModel->setDefault($this->userId, $id);
        return $this->respond(['message' => 'Default payment method updated']);
    }

    public function delete(int $id)
    {
        $method = $this->paymentModel->find($id);
        if (! $method || $method['user_id'] !== $this->userId) {
            return $this->failNotFound('Payment method not found');
        }

        if ($method['is_default']) {
            return $this->fail('Cannot delete default payment method. Set another default first.', 409);
        }

        $this->paymentModel->update($id, ['status' => 'inactive']);
        return $this->respondDeleted(['message' => 'Payment method removed']);
    }
}
