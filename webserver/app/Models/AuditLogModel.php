<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'entity_type', 'entity_id', 'action', 'actor_type',
        'actor_id', 'changes_json', 'ip_address', 'created_at',
    ];

    public function log(string $entityType, int $entityId, string $action, string $actorType, ?int $actorId, ?array $changes = null): int
    {
        return $this->insert([
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'action'      => $action,
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'changes_json' => $changes ? json_encode($changes) : null,
            'ip_address'  => service('request')->getIPAddress(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getForEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        return $this->where('entity_type', $entityType)
                     ->where('entity_id', $entityId)
                     ->orderBy('created_at', 'DESC')
                     ->limit($limit)
                     ->findAll();
    }
}
