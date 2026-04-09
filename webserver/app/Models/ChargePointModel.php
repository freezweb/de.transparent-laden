<?php

namespace App\Models;

use CodeIgniter\Model;

class ChargePointModel extends Model
{
    protected $table            = 'charge_points';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'provider_id', 'external_id', 'name', 'address', 'city',
        'postal_code', 'country', 'latitude', 'longitude',
        'operator_name', 'is_active', 'last_seen_at',
    ];

    public function findNearby(float $lat, float $lng, float $radiusKm = 25, int $limit = 50): array
    {
        $earthRadius = 6371;
        $sql = "SELECT cp.*, 
                ({$earthRadius} * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance
                FROM charge_points cp
                WHERE cp.is_active = 1 AND cp.latitude IS NOT NULL AND cp.longitude IS NOT NULL
                HAVING distance < ?
                ORDER BY distance ASC
                LIMIT ?";

        return $this->db->query($sql, [$lat, $lng, $lat, $radiusKm, $limit])->getResultArray();
    }

    public function findByProviderAndExternalId(int $providerId, string $externalId): ?array
    {
        return $this->where('provider_id', $providerId)
                     ->where('external_id', $externalId)
                     ->first();
    }
}
