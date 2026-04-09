<?php

namespace App\Controllers\Api;

use App\Models\UserDeviceModel;

class DeviceController extends ApiBaseController
{
    private UserDeviceModel $deviceModel;

    public function __construct()
    {
        $this->deviceModel = model(UserDeviceModel::class);
    }

    public function register()
    {
        $rules = [
            'platform'   => 'required|in_list[android,ios,web]',
            'push_token' => 'required|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        $deviceId = $this->deviceModel->registerOrUpdate(
            $this->userId,
            $data['platform'],
            $data['push_token'],
            $data['device_name'] ?? null,
            $data['app_version'] ?? null
        );

        return $this->respondCreated(['device_id' => $deviceId]);
    }

    public function index()
    {
        $devices = $this->deviceModel->getActiveForUser($this->userId);
        return $this->respond(['devices' => $devices]);
    }

    public function delete(int $id)
    {
        $device = $this->deviceModel->find($id);
        if (! $device || $device['user_id'] !== $this->userId) {
            return $this->failNotFound('Device not found');
        }

        $this->deviceModel->update($id, ['is_active' => 0]);
        return $this->respondDeleted(['message' => 'Device removed']);
    }
}
