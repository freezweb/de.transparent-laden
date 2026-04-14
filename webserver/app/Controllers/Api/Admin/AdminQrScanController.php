<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\ApiBaseController;
use App\Models\QrScanLogModel;

class AdminQrScanController extends ApiBaseController
{
    private QrScanLogModel $scanModel;

    public function __construct()
    {
        $this->scanModel = model(QrScanLogModel::class);
    }

    public function index()
    {
        $limit = min(200, (int) ($this->request->getGet('limit') ?? 100));
        $filter = $this->request->getGet('filter'); // 'unrecognized' or null

        if ($filter === 'unrecognized') {
            $scans = $this->scanModel->getUnrecognized($limit);
        } else {
            $scans = $this->scanModel->getRecent($limit);
        }

        return $this->respond(['scans' => $scans]);
    }
}
