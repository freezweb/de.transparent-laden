<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\ApiBaseController;
use App\Models\ReviewModel;
use App\Models\ReviewImageModel;

class AdminReviewController extends ApiBaseController
{
    private ReviewModel $reviewModel;
    private ReviewImageModel $imageModel;

    public function __construct()
    {
        $this->reviewModel = model(ReviewModel::class);
        $this->imageModel  = model(ReviewImageModel::class);
    }

    public function index()
    {
        $status = $this->request->getGet('status') ?? 'active';
        $limit  = min(200, (int) ($this->request->getGet('limit') ?? 50));

        $reviews = $this->reviewModel
            ->select('reviews.*, users.email as user_email, charge_points.name as charge_point_name')
            ->join('users', 'users.id = reviews.user_id', 'left')
            ->join('charge_points', 'charge_points.id = reviews.charge_point_id', 'left')
            ->where('reviews.status', $status)
            ->orderBy('reviews.created_at', 'DESC')
            ->limit($limit)
            ->findAll();

        return $this->respond(['reviews' => $reviews]);
    }

    public function moderate(int $id)
    {
        $review = $this->reviewModel->find($id);
        if (! $review) {
            return $this->failNotFound('Review nicht gefunden');
        }

        $json   = $this->request->getJSON();
        $status = $json->status ?? null;

        if (! in_array($status, ['active', 'hidden', 'removed'])) {
            return $this->failValidationErrors(['status' => 'Ungültiger Status']);
        }

        $this->reviewModel->moderate($id, $status);

        return $this->respond(['message' => 'Review aktualisiert', 'status' => $status]);
    }

    public function destroy(int $id)
    {
        $review = $this->reviewModel->find($id);
        if (! $review) {
            return $this->failNotFound('Review nicht gefunden');
        }

        // Delete images first
        $images = $this->imageModel->getForReview($id);
        foreach ($images as $img) {
            $path = WRITEPATH . 'uploads/reviews/' . $img['file_path'];
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->imageModel->where('review_id', $id)->delete();
        $this->reviewModel->delete($id);

        return $this->respond(['message' => 'Review gelöscht']);
    }
}
