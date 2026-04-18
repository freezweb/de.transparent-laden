<?php

namespace App\Models;

use CodeIgniter\Model;

class ConnectorModel extends Model
{
    protected $table            = 'connectors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'charge_point_id', 'external_id', 'connector_type', 'power_kw',
        'status', 'structured_tariff_json', 'last_status_update',
    ];

    public function getForChargePoint(int $chargePointId): array
    {
        return $this->where('charge_point_id', $chargePointId)->findAll();
    }

    public function getAvailableForChargePoint(int $chargePointId): array
    {
        return $this->where('charge_point_id', $chargePointId)
                     ->where('status', 'available')
                     ->findAll();
    }

    /**
     * Get connectors for multiple charge points in one query, grouped by charge_point_id.
     */
    public function getGroupedByChargePoints(array $chargePointIds): array
    {
        if (empty($chargePointIds)) return [];

        $connectors = $this->select('id, charge_point_id, connector_type, power_kw')
                           ->whereIn('charge_point_id', $chargePointIds)
                           ->findAll();

        $grouped = [];
        foreach ($connectors as $c) {
            $grouped[(int)$c['charge_point_id']][] = $c;
        }
        return $grouped;
    }
}
