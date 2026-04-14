<?php

namespace App\Controllers\Api;

use App\Models\ChargePointModel;
use App\Models\ConnectorModel;
use App\Models\PricingSnapshotModel;
use App\Libraries\PricingEngine;
use App\Libraries\Provider\ProviderFactory;
use App\Libraries\Entities\StructuredTariff;

class ChargePointController extends ApiBaseController
{
    private ChargePointModel $chargePointModel;
    private ConnectorModel $connectorModel;
    private PricingSnapshotModel $snapshotModel;

    public function __construct()
    {
        $this->chargePointModel = model(ChargePointModel::class);
        $this->connectorModel   = model(ConnectorModel::class);
        $this->snapshotModel    = model(PricingSnapshotModel::class);
    }

    public function nearby()
    {
        $lat = (float) $this->request->getGet('lat');
        $lng = (float) $this->request->getGet('lng');
        $radius = (float) ($this->request->getGet('radius') ?? 25);
        $limit = min(100, (int) ($this->request->getGet('limit') ?? 50));

        if (! $lat || ! $lng) {
            return $this->failValidationErrors(['lat' => 'Required', 'lng' => 'Required']);
        }

        $minPowerKw = $this->request->getGet('min_power_kw') ? (float) $this->request->getGet('min_power_kw') : null;

        $chargePoints = $this->chargePointModel->findNearby($lat, $lng, $radius, $limit);

        $result = [];
        foreach ($chargePoints as &$cp) {
            $cp['connectors'] = $this->connectorModel->getForChargePoint($cp['id']);

            // Filter by minimum charging power if requested
            if ($minPowerKw !== null) {
                $hasMatchingConnector = false;
                foreach ($cp['connectors'] as $conn) {
                    if ((float) ($conn['power_kw'] ?? 0) >= $minPowerKw) {
                        $hasMatchingConnector = true;
                        break;
                    }
                }
                if (! $hasMatchingConnector) {
                    continue;
                }
            }

            $result[] = $cp;
        }

        return $this->respond(['charge_points' => $result]);
    }

    public function show(int $id)
    {
        $cp = $this->chargePointModel->find($id);
        if (! $cp) {
            return $this->failNotFound('Charge point not found');
        }

        $connectors = $this->connectorModel->getForChargePoint($id);

        foreach ($connectors as &$conn) {
            $snapshot = $this->snapshotModel->getLatestForConnector($conn['id']);
            $conn['pricing'] = $snapshot ? [
                'energy_price_per_kwh_cent' => $snapshot['energy_price_per_kwh_cent'],
                'time_price_per_min_cent'   => $snapshot['time_price_per_min_cent'],
                'blocking_fee_per_min_cent' => $snapshot['blocking_fee_per_min_cent'],
                'platform_fee_effective_cent' => $snapshot['platform_fee_effective_cent'],
                'estimated_total_per_kwh_cent' => $snapshot['estimated_total_per_kwh_cent'],
                'transparency' => json_decode($snapshot['transparency_json'], true),
            ] : null;
        }

        $cp['connectors'] = $connectors;
        return $this->respond(['charge_point' => $cp]);
    }

    public function pricing(int $connectorId)
    {
        $connector = $this->connectorModel->find($connectorId);
        if (! $connector) {
            return $this->failNotFound('Connector not found');
        }

        $chargePoint = $this->chargePointModel->find($connector['charge_point_id']);
        $providerModel = model(\App\Models\ProviderModel::class);
        $provider = $providerModel->find($chargePoint['provider_id']);

        $paymentType = $this->request->getGet('payment_type') ?? 'credit_card';

        try {
            $adapter = ProviderFactory::create($provider);
            $tariff = $adapter->getTariff($connector['external_id']);
        } catch (\Exception $e) {
            $tariff = StructuredTariff::fromJson($connector['structured_tariff_json']);
        }

        $pricingEngine = new PricingEngine();
        $result = $pricingEngine->calculatePrice(
            $tariff, $provider, $this->userId, $paymentType, $connectorId
        );

        return $this->respond([
            'pricing' => $result->toSnapshotData(),
            'transparency' => $result->transparencyBreakdown,
        ]);
    }
}
