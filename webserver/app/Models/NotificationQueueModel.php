<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationQueueModel extends Model
{
    protected $table            = 'notification_queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'session_id', 'event_type', 'payload_json',
        'target_device_ids_json', 'attempt_count', 'max_attempts',
        'next_attempt_at', 'last_error', 'status',
    ];

    public function getPendingBatch(int $batchSize = 50): array
    {
        return $this->whereIn('status', ['pending', 'processing'])
                     ->where('next_attempt_at <=', date('Y-m-d H:i:s'))
                     ->where('attempt_count < max_attempts', null, false)
                     ->orderBy('created_at', 'ASC')
                     ->limit($batchSize)
                     ->findAll();
    }

    public function markProcessing(int $id): bool
    {
        return $this->update($id, ['status' => 'processing']);
    }

    public function markSent(int $id): bool
    {
        return $this->update($id, ['status' => 'sent']);
    }

    public function markFailed(int $id, string $error): bool
    {
        $item = $this->find($id);
        $attempts = ($item['attempt_count'] ?? 0) + 1;
        $data = [
            'attempt_count' => $attempts,
            'last_error'    => $error,
        ];

        if ($attempts >= ($item['max_attempts'] ?? 3)) {
            $data['status'] = 'failed';
        } else {
            $data['status'] = 'pending';
            $data['next_attempt_at'] = date('Y-m-d H:i:s', strtotime('+' . ($attempts * 60) . ' seconds'));
        }

        return $this->update($id, $data);
    }

    public function cleanupSent(int $daysOld = 7): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        return $this->where('status', 'sent')
                     ->where('updated_at <', $cutoff)
                     ->delete();
    }
}
