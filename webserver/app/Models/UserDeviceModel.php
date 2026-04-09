<?php

namespace App\Models;

use CodeIgniter\Model;

class UserDeviceModel extends Model
{
    protected $table            = 'user_devices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'platform', 'push_token', 'device_name',
        'app_version', 'notifications_enabled', 'is_active', 'last_seen_at',
    ];

    public function getActiveForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                     ->where('is_active', 1)
                     ->where('notifications_enabled', 1)
                     ->findAll();
    }

    public function registerOrUpdate(int $userId, string $platform, string $pushToken, ?string $deviceName, ?string $appVersion): int
    {
        $existing = $this->where('push_token', $pushToken)->first();
        $data = [
            'user_id'     => $userId,
            'platform'    => $platform,
            'push_token'  => $pushToken,
            'device_name' => $deviceName,
            'app_version' => $appVersion,
            'is_active'   => 1,
            'last_seen_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }

        return $this->insert($data);
    }

    public function cleanupInactive(int $daysInactive = 90): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$daysInactive} days"));
        return $this->where('last_seen_at <', $cutoff)->delete();
    }
}
