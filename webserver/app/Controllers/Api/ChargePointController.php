<?php

namespace App\Controllers\Api;

use App\Models\ChargePointModel;
use App\Models\ConnectorModel;
use App\Models\PricingSnapshotModel;
use App\Libraries\PricingEngine;
use App\Libraries\Provider\ProviderFactory;
use App\Libraries\Entities\StructuredTariff;
use App\Libraries\OverpassService;

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

    /**
     * GET /charge-points/nearby?lat_min=...&lng_min=...&lat_max=...&lng_max=...
     * Also supports legacy ?lat=...&lng=...&radius=...
     * Returns local DB + Overpass (OSM) stations merged.
     */
    public function nearby()
    {
        // Bounding box params (preferred)
        $latMin = $this->request->getGet('lat_min') !== null ? (float) $this->request->getGet('lat_min') : null;
        $lngMin = $this->request->getGet('lng_min') !== null ? (float) $this->request->getGet('lng_min') : null;
        $latMax = $this->request->getGet('lat_max') !== null ? (float) $this->request->getGet('lat_max') : null;
        $lngMax = $this->request->getGet('lng_max') !== null ? (float) $this->request->getGet('lng_max') : null;

        // Legacy lat/lng/radius fallback
        $lat    = $this->request->getGet('lat') !== null ? (float) $this->request->getGet('lat') : null;
        $lng    = $this->request->getGet('lng') !== null ? (float) $this->request->getGet('lng') : null;
        $radius = (float) ($this->request->getGet('radius') ?? 25);

        $useBBox = ($latMin !== null && $lngMin !== null && $latMax !== null && $lngMax !== null);

        if (! $useBBox && (! $lat || ! $lng)) {
            return $this->failValidationErrors(['lat' => 'Required (or provide lat_min/lng_min/lat_max/lng_max)']);
        }

        // Convert lat/lng/radius to bbox if needed
        if (! $useBBox) {
            $latDelta = $radius / 111.32;
            $lngDelta = $radius / (111.32 * cos(deg2rad($lat)));
            $latMin = $lat - $latDelta;
            $latMax = $lat + $latDelta;
            $lngMin = $lng - $lngDelta;
            $lngMax = $lng + $lngDelta;
        }

        // Clamp bbox size to prevent abuse (max ~0.5 degrees ~ 55km)
        $latSpan = abs($latMax - $latMin);
        $lngSpan = abs($lngMax - $lngMin);
        if ($latSpan > 0.5) {
            $mid = ($latMin + $latMax) / 2;
            $latMin = $mid - 0.25;
            $latMax = $mid + 0.25;
        }
        if ($lngSpan > 0.5) {
            $mid = ($lngMin + $lngMax) / 2;
            $lngMin = $mid - 0.25;
            $lngMax = $mid + 0.25;
        }

        $limit = min(500, (int) ($this->request->getGet('limit') ?? 200));

        $minPowerKw      = $this->request->getGet('min_power_kw') ? (float) $this->request->getGet('min_power_kw') : null;
        $maxPowerKw      = $this->request->getGet('max_power_kw') ? (float) $this->request->getGet('max_power_kw') : null;
        $connectorType   = $this->request->getGet('connector_type') ?: null;
        $currentCategory = $this->request->getGet('current_category') ?: null;
        $onlyStartable   = $this->request->getGet('only_startable');

        // 1) Local DB charge points in bbox
        $chargePoints = $this->chargePointModel->findByBoundingBox($latMin, $lngMin, $latMax, $lngMax, $limit);

        if ($onlyStartable === '1' || $onlyStartable === 'true') {
            $chargePoints = array_filter($chargePoints, fn($cp) => ! empty($cp['is_startable']));
        }

        $result = [];
        foreach ($chargePoints as &$cp) {
            $cp['connectors'] = $this->connectorModel->getForChargePoint($cp['id']);
            if (! $this->matchesConnectorFilters($cp['connectors'], $minPowerKw, $maxPowerKw, $connectorType, $currentCategory)) {
                continue;
            }
            $cp['is_startable'] = isset($cp['is_startable']) ? (bool) $cp['is_startable'] : false;
            $cp['source'] = 'local';
            $result[] = $cp;
        }

        // 2) Overpass (OSM) external stations — skip if only_startable
        if ($onlyStartable !== '1' && $onlyStartable !== 'true') {
            try {
                $overpass = new OverpassService();
                $osmStations = $overpass->getStationsInBBox($latMin, $lngMin, $latMax, $lngMax, $limit);

                foreach ($osmStations as $osm) {
                    // Apply filters
                    if ($minPowerKw !== null && $osm['max_power_kw'] < $minPowerKw) continue;
                    if ($maxPowerKw !== null && $osm['max_power_kw'] > $maxPowerKw) continue;
                    if (! $this->matchesConnectorFilters($osm['connectors'], $minPowerKw, $maxPowerKw, $connectorType, $currentCategory)) {
                        continue;
                    }
                    $result[] = $osm;
                }
            } catch (\Throwable $e) {
                log_message('warning', 'Overpass fetch failed: ' . $e->getMessage());
            }
        }

        return $this->respond(['charge_points' => array_values($result)]);
    }

    /**
     * Check if any connector matches the given filters.
     */
    private function matchesConnectorFilters(array $connectors, ?float $minPowerKw, ?float $maxPowerKw, ?string $connectorType, ?string $currentCategory): bool
    {
        if ($minPowerKw === null && $maxPowerKw === null && $connectorType === null && $currentCategory === null) {
            return true;
        }

        foreach ($connectors as $conn) {
            $power = (float) ($conn['power_kw'] ?? 0);
            $type  = $conn['connector_type'] ?? '';

            if ($minPowerKw !== null && $power < $minPowerKw) continue;
            if ($maxPowerKw !== null && $power > $maxPowerKw) continue;
            if ($connectorType !== null && strcasecmp($type, $connectorType) !== 0) continue;
            if ($currentCategory === 'AC' && in_array($type, ['CCS', 'CHAdeMO'])) continue;
            if ($currentCategory === 'DC' && in_array($type, ['Type2', 'Type1', 'Schuko'])) continue;

            return true;
        }

        return false;
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
        $cp['is_startable'] = isset($cp['is_startable']) ? (bool) $cp['is_startable'] : null;
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
