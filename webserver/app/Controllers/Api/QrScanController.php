<?php

namespace App\Controllers\Api;

use App\Models\QrScanLogModel;
use App\Models\ChargePointModel;
use App\Models\ConnectorModel;

class QrScanController extends ApiBaseController
{
    private QrScanLogModel $scanModel;
    private ChargePointModel $chargePointModel;

    public function __construct()
    {
        $this->scanModel        = model(QrScanLogModel::class);
        $this->chargePointModel = model(ChargePointModel::class);
    }

    public function store()
    {
        $rules = [
            'qr_content' => 'required|string|max_length[2000]',
            'latitude'   => 'permit_empty|decimal',
            'longitude'  => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $json = $this->request->getJSON();
        $qrContent = $json->qr_content;

        // Try to match QR content to a known charge point
        $matched = $this->matchChargePoint($qrContent);

        $logData = [
            'user_id'         => $this->userId,
            'qr_content'      => $qrContent,
            'latitude'        => $json->latitude ?? null,
            'longitude'       => $json->longitude ?? null,
            'recognized'      => $matched ? 1 : 0,
            'charge_point_id' => $matched['id'] ?? null,
            'provider_name'   => $matched['provider_name'] ?? null,
            'is_startable'    => $matched['is_startable'] ?? null,
        ];

        $this->scanModel->log($logData);

        if ($matched) {
            return $this->respond([
                'recognized'      => true,
                'charge_point_id' => (int) $matched['id'],
                'name'            => $matched['name'],
                'is_startable'    => (bool) ($matched['is_startable'] ?? false),
            ]);
        }

        return $this->respond([
            'recognized' => false,
            'message'    => 'QR-Code nicht erkannt. Der Scan wurde protokolliert.',
        ]);
    }

    private function matchChargePoint(string $qrContent): ?array
    {
        // Try matching by external_id
        $cp = $this->chargePointModel->where('external_id', $qrContent)->first();
        if ($cp) {
            $provider = model(\App\Models\ProviderModel::class)->find($cp['provider_id']);
            $cp['provider_name'] = $provider['name'] ?? null;
            return $cp;
        }

        // Try matching partial (URL might contain external_id)
        $allCps = $this->chargePointModel->where('is_active', 1)->findAll();
        foreach ($allCps as $cp) {
            if (! empty($cp['external_id']) && strpos($qrContent, $cp['external_id']) !== false) {
                $provider = model(\App\Models\ProviderModel::class)->find($cp['provider_id']);
                $cp['provider_name'] = $provider['name'] ?? null;
                return $cp;
            }
        }

        return null;
    }
}
