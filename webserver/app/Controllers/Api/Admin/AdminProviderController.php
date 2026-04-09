<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\ApiBaseController;
use App\Models\ProviderModel;
use App\Models\AuditLogModel;
use App\Libraries\Provider\ProviderFactory;

class AdminProviderController extends ApiBaseController
{
    private ProviderModel $providerModel;
    private AuditLogModel $auditModel;

    public function __construct()
    {
        $this->providerModel = model(ProviderModel::class);
        $this->auditModel    = model(AuditLogModel::class);
    }

    public function index()
    {
        $providers = $this->providerModel->findAll();
        $safe = array_map(function ($p) {
            unset($p['config_encrypted']);
            return $p;
        }, $providers);

        return $this->respond(['providers' => $safe]);
    }

    public function show(int $id)
    {
        $provider = $this->providerModel->find($id);
        if (! $provider) {
            return $this->failNotFound('Provider not found');
        }

        unset($provider['config_encrypted']);
        return $this->respond(['provider' => $provider]);
    }

    public function store()
    {
        $rules = [
            'name'           => 'required|max_length[200]',
            'slug'           => 'required|alpha_dash|max_length[100]|is_unique[providers.slug]',
            'adapter_class'  => 'required|max_length[200]',
            'api_base_url'   => 'permit_empty|valid_url_strict',
            'roaming_fee_type'  => 'required|in_list[none,fixed_per_kwh,percentage]',
            'roaming_fee_value' => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        $configEncrypted = null;
        if (! empty($data['config'])) {
            $encrypter       = \Config\Services::encrypter();
            $configEncrypted = base64_encode($encrypter->encrypt(json_encode($data['config'])));
        }

        $providerId = $this->providerModel->insert([
            'name'              => $data['name'],
            'slug'              => $data['slug'],
            'adapter_class'     => $data['adapter_class'],
            'api_base_url'      => $data['api_base_url'] ?? null,
            'config_encrypted'  => $configEncrypted,
            'roaming_fee_type'  => $data['roaming_fee_type'],
            'roaming_fee_value' => $data['roaming_fee_value'] ?? 0,
            'is_active'         => 1,
        ]);

        $this->auditModel->log('providers', $providerId, 'admin', $this->userId, 'create', [
            'slug' => $data['slug'],
        ]);

        return $this->respondCreated(['provider_id' => $providerId]);
    }

    public function update(int $id)
    {
        $provider = $this->providerModel->find($id);
        if (! $provider) {
            return $this->failNotFound('Provider not found');
        }

        $data    = $this->request->getJSON(true);
        $updates = [];

        foreach (['name', 'api_base_url', 'roaming_fee_type', 'roaming_fee_value', 'is_active'] as $field) {
            if (isset($data[$field])) {
                $updates[$field] = $data[$field];
            }
        }

        if (! empty($data['config'])) {
            $encrypter            = \Config\Services::encrypter();
            $updates['config_encrypted'] = base64_encode($encrypter->encrypt(json_encode($data['config'])));
        }

        if (! empty($updates)) {
            $this->providerModel->update($id, $updates);
            $this->auditModel->log('providers', $id, 'admin', $this->userId, 'update', array_keys($updates));
        }

        return $this->respond(['message' => 'Provider updated']);
    }

    public function sync(int $id)
    {
        $provider = $this->providerModel->find($id);
        if (! $provider) {
            return $this->failNotFound('Provider not found');
        }

        try {
            $factory = new ProviderFactory();
            $adapter = $factory->getAdapter($provider['slug']);

            $capabilities = $adapter->getCapabilities();
            if (! $capabilities->supportsLocationSync) {
                return $this->fail('Provider does not support location sync', 400);
            }

            $result = $adapter->syncLocations();

            $this->auditModel->log('providers', $id, 'admin', $this->userId, 'sync', [
                'result' => $result,
            ]);

            return $this->respond([
                'message' => 'Sync completed',
                'result'  => $result,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Provider sync failed: ' . $e->getMessage());
            return $this->fail('Sync failed: ' . $e->getMessage(), 500);
        }
    }
}
