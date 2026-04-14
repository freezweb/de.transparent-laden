<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentReportModel extends Model
{
    protected $table            = 'content_reports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'reporter_user_id', 'entity_type', 'entity_id',
        'reason', 'status', 'moderator_notes',
    ];

    public function getPending(int $limit = 100): array
    {
        return $this->where('status', 'pending')
                     ->orderBy('created_at', 'DESC')
                     ->limit($limit)
                     ->findAll();
    }

    public function moderate(int $id, string $status, ?string $notes = null): bool
    {
        $data = ['status' => $status];
        if ($notes !== null) {
            $data['moderator_notes'] = $notes;
        }
        return $this->update($id, $data);
    }

    public function getForEntity(string $entityType, int $entityId): array
    {
        return $this->where('entity_type', $entityType)
                     ->where('entity_id', $entityId)
                     ->orderBy('created_at', 'DESC')
                     ->findAll();
    }
}
