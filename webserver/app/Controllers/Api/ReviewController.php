<?php

namespace App\Controllers\Api;

use App\Models\ReviewModel;
use App\Models\ReviewImageModel;

class ReviewController extends ApiBaseController
{
    private ReviewModel $reviewModel;
    private ReviewImageModel $imageModel;

    public function __construct()
    {
        $this->reviewModel = model(ReviewModel::class);
        $this->imageModel  = model(ReviewImageModel::class);
    }

    public function index(int $chargePointId)
    {
        $reviews = $this->reviewModel->getForChargePoint($chargePointId);

        $reviewIds = array_column($reviews, 'id');
        $allImages = $this->imageModel->getForReviews($reviewIds);

        $imagesByReview = [];
        foreach ($allImages as $img) {
            $imagesByReview[$img['review_id']][] = base_url('uploads/reviews/' . $img['file_path']);
        }

        foreach ($reviews as &$review) {
            $review['images'] = $imagesByReview[$review['id']] ?? [];
        }

        $avgRating = $this->reviewModel->getAverageRating($chargePointId);

        return $this->respond([
            'reviews'        => $reviews,
            'average_rating' => $avgRating,
            'total'          => count($reviews),
        ]);
    }

    public function store(int $chargePointId)
    {
        $rules = [
            'rating'  => 'required|integer|greater_than[0]|less_than[6]',
            'comment' => 'permit_empty|string|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $reviewId = $this->reviewModel->insert([
            'user_id'         => $this->userId,
            'charge_point_id' => $chargePointId,
            'rating'          => (int) $this->request->getJSON()->rating,
            'comment'         => $this->request->getJSON()->comment ?? null,
            'status'          => 'active',
        ]);

        return $this->respondCreated(['id' => $reviewId, 'message' => 'Review erstellt']);
    }

    public function uploadImage(int $reviewId)
    {
        $review = $this->reviewModel->find($reviewId);
        if (! $review || (int) $review['user_id'] !== $this->userId) {
            return $this->failNotFound('Review nicht gefunden');
        }

        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid()) {
            return $this->failValidationErrors(['image' => 'Bild erforderlich']);
        }

        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
            return $this->failValidationErrors(['image' => 'Nur JPEG, PNG oder WebP erlaubt']);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->failValidationErrors(['image' => 'Maximale Dateigröße: 5 MB']);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/reviews', $newName);

        $imageId = $this->imageModel->insert([
            'review_id'  => $reviewId,
            'file_path'  => $newName,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->respondCreated([
            'id'  => $imageId,
            'url' => base_url('uploads/reviews/' . $newName),
        ]);
    }
}
