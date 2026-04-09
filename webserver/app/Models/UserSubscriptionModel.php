<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSubscriptionModel extends Model
{
    protected $table            = 'user_subscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'plan_version_id', 'status', 'billing_cycle',
        'starts_at', 'current_period_end', 'cancelled_at',
    ];

    public function getActiveForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
                     ->where('status', 'active')
                     ->first();
    }

    public function getExpiring(string $beforeDate): array
    {
        return $this->where('status', 'active')
                     ->where('current_period_end <=', $beforeDate)
                     ->findAll();
    }
}
