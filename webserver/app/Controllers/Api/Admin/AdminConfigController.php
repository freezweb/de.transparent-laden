<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\ApiBaseController;
use App\Models\SystemConfigModel;
use App\Models\AuditLogModel;

class AdminConfigController extends ApiBaseController
{
    private SystemConfigModel $configModel;
    private AuditLogModel     $auditModel;

    public function __construct()
    {
        $this->configModel = model(SystemConfigModel::class);
        $this->auditModel  = model(AuditLogModel::class);
    }

    public function index()
    {
        $configs = $this->configModel->findAll();
        return $this->respond(['configs' => $configs]);
    }

    public function show(string $key)
    {
        $config = $this->configModel->where('config_key', $key)->first();
        if (! $config) {
            return $this->failNotFound('Config key not found');
        }

        return $this->respond(['config' => $config]);
    }

    public function update()
    {
        $rules = [
            'configs'               => 'required',
            'configs.*.config_key'  => 'required|max_length[100]',
            'configs.*.config_value' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        foreach ($data['configs'] as $cfg) {
            $old = $this->configModel->getValue($cfg['config_key']);
            $this->configModel->setValue($cfg['config_key'], $cfg['config_value'], $this->userId);

            $this->auditModel->log('system_config', 0, 'admin', $this->userId, 'update_config', [
                'key'       => $cfg['config_key'],
                'old_value' => $old,
                'new_value' => $cfg['config_value'],
            ]);
        }

        return $this->respond(['message' => 'Configuration updated']);
    }
}
