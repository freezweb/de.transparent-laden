<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewImageModel extends Model
{
    protected $table            = 'review_images';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'review_id', 'file_path', 'thumbnail_path', 'created_at',
    ];

    public function getForReview(int $reviewId): array
    {
        return $this->where('review_id', $reviewId)->findAll();
    }

    public function getForReviews(array $reviewIds): array
    {
        if (empty($reviewIds)) {
            return [];
        }
        return $this->whereIn('review_id', $reviewIds)->findAll();
    }
}
