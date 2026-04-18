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
     * GET /charge-points/locations
     * Returns ALL active local charge points with static data only.
     * Ultra-fast: single DB query + batch connector summary. No status, no Overpass.
     * Designed for preloading all markers on app start.
     */
    public function locations()
    {
        $cacheKey = 'charge_point_locations';
        $cached = cache($cacheKey);
        if ($cached !== null) {
            return $this->respond($cached);
        }

        $chargePoints = $this->chargePointModel->getAllActive();
        $cpIds = array_column($chargePoints, 'id');

        // Batch-load connector summaries in ONE query
        $grouped = $this->connectorModel->getGroupedByChargePoints(array_map('intval', $cpIds));

        $result = [];
        foreach ($chargePoints as $cp) {
            $connectors = $grouped[(int)$cp['id']] ?? [];
            $maxPwr = 0;
            $types = [];
            foreach ($connectors as $c) {
                $pwr = (float)($c['power_kw'] ?? 0);
                if ($pwr > $maxPwr) $maxPwr = $pwr;
                if (!empty($c['connector_type'])) {
                    $types[] = $c['connector_type'];
                }
            }

            $result[] = [
                'id'              => (int)$cp['id'],
                'name'            => $cp['name'],
                'latitude'        => $cp['latitude'],
                'longitude'       => $cp['longitude'],
                'address'         => $cp['address'],
                'city'            => $cp['city'],
                'postal_code'     => $cp['postal_code'],
                'operator_name'   => $cp['operator_name'],
                'is_startable'    => (bool)($cp['is_startable'] ?? false),
                'max_power_kw'    => $maxPwr,
                'connector_count' => count($connectors),
                'connector_types' => array_values(array_unique($types)),
                'source'          => 'local',
            ];
        }

        $response = ['charge_points' => $result, 'count' => count($result)];

        // Cache for 5 minutes server-side
        cache()->save($cacheKey, $response, 300);

        return $this->respond($response);
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

            // Availability: count total and available connectors
            $cp['total_connectors'] = count($cp['connectors']);
            $cp['available_connectors'] = 0;
            $maxPwr = 0;
            foreach ($cp['connectors'] as $c) {
                if (($c['status'] ?? '') === 'available') {
                    $cp['available_connectors']++;
                }
                $pwr = (float) ($c['power_kw'] ?? 0);
                if ($pwr > $maxPwr) $maxPwr = $pwr;
            }
            $cp['max_power_kw'] = $maxPwr;

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

    /**
     * GET /charge-points/status?ids=1,2,3
     * Returns only dynamic status data for known local stations.
     */
    public function status()
    {
        $idsParam = $this->request->getGet('ids');
        if (empty($idsParam)) {
            return $this->failValidationErrors(['ids' => 'Required (comma-separated)']);
        }

        $ids = array_filter(array_map('intval', explode(',', $idsParam)));
        if (empty($ids)) {
            return $this->respond(['statuses' => new \stdClass()]);
        }

        // Limit to 100 IDs per request
        $ids = array_slice($ids, 0, 100);

        $statuses = [];
        foreach ($ids as $id) {
            $connectors = $this->connectorModel->getForChargePoint($id);
            $total = count($connectors);
            $available = 0;
            $occupied = 0;
            $outOfService = 0;
            foreach ($connectors as $c) {
                $s = $c['status'] ?? 'unknown';
                if ($s === 'available') $available++;
                elseif ($s === 'occupied') $occupied++;
                elseif ($s === 'out_of_service') $outOfService++;
            }

            $cp = $this->chargePointModel->find($id);

            $statuses[(string)$id] = [
                'total_connectors'     => $total,
                'available_connectors' => $available,
                'occupied_connectors'  => $occupied,
                'out_of_service'       => $outOfService,
                'is_startable'         => isset($cp['is_startable']) ? (bool) $cp['is_startable'] : false,
                'last_status_update'   => $connectors[0]['last_status_update'] ?? null,
                'connectors'           => array_map(fn($c) => [
                    'id'             => $c['id'],
                    'connector_type' => $c['connector_type'],
                    'power_kw'       => $c['power_kw'],
                    'status'         => $c['status'] ?? 'unknown',
                ], $connectors),
            ];
        }

        return $this->respond(['statuses' => $statuses]);
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
