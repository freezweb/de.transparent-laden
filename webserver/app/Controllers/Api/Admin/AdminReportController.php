<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\ApiBaseController;
use App\Models\ContentReportModel;

class AdminReportController extends ApiBaseController
{
    private ContentReportModel $reportModel;

    public function __construct()
    {
        $this->reportModel = model(ContentReportModel::class);
    }

    public function index()
    {
        $status = $this->request->getGet('status') ?? 'pending';
        $limit  = min(200, (int) ($this->request->getGet('limit') ?? 50));

        $reports = $this->reportModel
            ->select('content_reports.*, users.email as reporter_email')
            ->join('users', 'users.id = content_reports.reporter_user_id', 'left')
            ->where('content_reports.status', $status)
            ->orderBy('content_reports.created_at', 'DESC')
            ->limit($limit)
            ->findAll();

        return $this->respond(['reports' => $reports]);
    }

    public function moderate(int $id)
    {
        $report = $this->reportModel->find($id);
        if (! $report) {
            return $this->failNotFound('Meldung nicht gefunden');
        }

        $json   = $this->request->getJSON();
        $status = $json->status ?? null;
        $notes  = $json->moderator_notes ?? null;

        if (! in_array($status, ['reviewed', 'dismissed', 'actioned'])) {
            return $this->failValidationErrors(['status' => 'Ungültiger Status']);
        }

        $this->reportModel->moderate($id, $status, $notes);

        return $this->respond(['message' => 'Meldung aktualisiert', 'status' => $status]);
    }
}
