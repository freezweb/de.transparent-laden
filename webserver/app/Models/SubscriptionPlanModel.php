<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionPlanModel extends Model
{
    protected $table            = 'subscription_plans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'name', 'slug', 'description', 'price_monthly_cent', 'price_yearly_cent',
        'platform_fee_reduction_percent', 'features_json', 'is_active', 'sort_order',
    ];

    public function getActivePlans(): array
    {
        return $this->where('is_active', 1)
                     ->orderBy('sort_order', 'ASC')
                     ->findAll();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }
}
