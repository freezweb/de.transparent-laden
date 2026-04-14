<?php

namespace App\Controllers\Api;

use App\Models\ContentReportModel;

class ReportController extends ApiBaseController
{
    private ContentReportModel $reportModel;

    public function __construct()
    {
        $this->reportModel = model(ContentReportModel::class);
    }

    public function store()
    {
        $rules = [
            'entity_type' => 'required|in_list[review,review_image,charge_point]',
            'entity_id'   => 'required|integer',
            'reason'      => 'required|string|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $json = $this->request->getJSON();

        $reportId = $this->reportModel->insert([
            'reporter_user_id' => $this->userId,
            'entity_type'      => $json->entity_type,
            'entity_id'        => (int) $json->entity_id,
            'reason'           => $json->reason,
            'status'           => 'pending',
        ]);

        return $this->respondCreated(['id' => $reportId, 'message' => 'Meldung eingegangen']);
    }
}
