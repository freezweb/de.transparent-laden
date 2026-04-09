<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionPlanVersionModel extends Model
{
    protected $table            = 'subscription_plan_versions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'plan_id', 'version', 'price_monthly_cent', 'price_yearly_cent',
        'platform_fee_reduction_percent', 'features_json', 'valid_from', 'valid_until',
    ];

    public function getCurrentVersion(int $planId): ?array
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('plan_id', $planId)
                     ->where('valid_from <=', $now)
                     ->groupStart()
                         ->where('valid_until IS NULL')
                         ->orWhere('valid_until >', $now)
                     ->groupEnd()
                     ->orderBy('version', 'DESC')
                     ->first();
    }
}
