<?php

namespace App\Models;

use CodeIgniter\Model;

class PricingSnapshotModel extends Model
{
    protected $table            = 'pricing_snapshots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $createdField     = 'created_at';

    protected $allowedFields = [
        'connector_id', 'provider_id', 'snapshot_time',
        'structured_tariff_json',
        'energy_price_per_kwh_cent', 'time_price_per_min_cent',
        'blocking_fee_per_min_cent', 'blocking_free_minutes',
        'start_fee_cent', 'min_billing_amount_cent',
        'roaming_fee_cent', 'platform_fee_cent',
        'platform_fee_reduction_percent', 'platform_fee_effective_cent',
        'payment_fee_cent', 'payment_fee_model_id',
        'estimated_total_per_kwh_cent', 'transparency_json',
    ];

    public function getLatestForConnector(int $connectorId): ?array
    {
        return $this->where('connector_id', $connectorId)
                     ->orderBy('snapshot_time', 'DESC')
                     ->first();
    }
}
