<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table            = 'reviews';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'charge_point_id', 'rating', 'comment', 'status',
    ];

    public function getForChargePoint(int $chargePointId, int $limit = 50): array
    {
        return $this->select('reviews.*, users.first_name as user_name')
                     ->join('users', 'users.id = reviews.user_id', 'left')
                     ->where('reviews.charge_point_id', $chargePointId)
                     ->where('reviews.status', 'active')
                     ->orderBy('reviews.created_at', 'DESC')
                     ->limit($limit)
                     ->findAll();
    }

    public function getAverageRating(int $chargePointId): ?float
    {
        $result = $this->selectAvg('rating')
                       ->where('charge_point_id', $chargePointId)
                       ->where('status', 'active')
                       ->first();
        return $result['rating'] ? (float) $result['rating'] : null;
    }

    public function getByUser(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
                     ->orderBy('created_at', 'DESC')
                     ->limit($limit)
                     ->findAll();
    }

    public function moderate(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }
}
