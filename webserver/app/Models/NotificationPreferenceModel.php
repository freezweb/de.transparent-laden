<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationPreferenceModel extends Model
{
    protected $table            = 'notification_preferences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'event_type', 'enabled',
    ];

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function isEnabled(int $userId, string $eventType): bool
    {
        $pref = $this->where('user_id', $userId)
                      ->where('event_type', $eventType)
                      ->first();

        // Default: enabled if no preference set
        return $pref ? (bool) $pref['enabled'] : true;
    }

    public function setPreference(int $userId, string $eventType, bool $enabled): bool
    {
        $existing = $this->where('user_id', $userId)
                          ->where('event_type', $eventType)
                          ->first();

        if ($existing) {
            return $this->update($existing['id'], ['enabled' => $enabled ? 1 : 0]);
        }

        return (bool) $this->insert([
            'user_id'    => $userId,
            'event_type' => $eventType,
            'enabled'    => $enabled ? 1 : 0,
        ]);
    }
}
