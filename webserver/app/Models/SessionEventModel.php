<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionEventModel extends Model
{
    protected $table            = 'session_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $createdField     = 'created_at';

    protected $allowedFields = [
        'session_id', 'event_type', 'event_data_json',
        'energy_kwh_at_event', 'live_cost_cent',
    ];

    public function getForSession(int $sessionId): array
    {
        return $this->where('session_id', $sessionId)
                     ->orderBy('created_at', 'ASC')
                     ->findAll();
    }

    public function logEvent(int $sessionId, string $eventType, ?array $data = null, float $energyKwh = 0, int $liveCostCent = 0): int
    {
        return $this->insert([
            'session_id'          => $sessionId,
            'event_type'          => $eventType,
            'event_data_json'     => $data ? json_encode($data) : null,
            'energy_kwh_at_event' => $energyKwh,
            'live_cost_cent'      => $liveCostCent,
            'created_at'          => date('Y-m-d H:i:s'),
        ]);
    }
}
