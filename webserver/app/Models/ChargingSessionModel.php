<?php

namespace App\Models;

use CodeIgniter\Model;

class ChargingSessionModel extends Model
{
    protected $table            = 'charging_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'connector_id', 'provider_id', 'payment_method_id',
        'payment_gateway', 'payment_gateway_ref', 'payment_captured',
        'pricing_snapshot_id', 'status', 'external_session_id',
        'started_at', 'stopped_at', 'energy_kwh', 'duration_seconds',
        'blocking_duration_seconds', 'energy_cost_cent', 'time_cost_cent',
        'blocking_cost_cent', 'start_fee_cent', 'roaming_fee_cent',
        'platform_fee_cent', 'payment_fee_cent', 'total_price_cent',
        'last_live_update_at', 'recovery_attempts', 'last_recovery_at',
        'failure_reason',
    ];

    public function getActiveForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
                     ->whereIn('status', ['pending', 'starting', 'active', 'stopping'])
                     ->first();
    }

    public function getHistoryForUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        return $this->where('user_id', $userId)
                     ->whereIn('status', ['completed', 'failed', 'cancelled'])
                     ->orderBy('created_at', 'DESC')
                     ->paginate($perPage, 'default', $page);
    }

    public function getStale(int $staleSinceMinutes = 30): array
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$staleSinceMinutes} minutes"));
        return $this->whereIn('status', ['starting', 'active', 'stopping'])
                     ->where('last_live_update_at <', $cutoff)
                     ->findAll();
    }

    public function getStuckForRecovery(int $maxRetries = 3): array
    {
        return $this->whereIn('status', ['starting', 'stopping'])
                     ->where('recovery_attempts <', $maxRetries)
                     ->where('updated_at <', date('Y-m-d H:i:s', strtotime('-5 minutes')))
                     ->findAll();
    }
}
