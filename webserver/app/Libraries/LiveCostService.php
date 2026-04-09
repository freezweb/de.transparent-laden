<?php

namespace App\Libraries;

use App\Libraries\Provider\ProviderFactory;
use App\Models\ChargingSessionModel;
use App\Models\PricingSnapshotModel;
use App\Models\SessionEventModel;

class LiveCostService
{
    private ChargingSessionModel $sessionModel;
    private PricingSnapshotModel $snapshotModel;
    private SessionEventModel $eventModel;
    private PricingEngine $pricingEngine;

    public function __construct()
    {
        $this->sessionModel  = model(ChargingSessionModel::class);
        $this->snapshotModel = model(PricingSnapshotModel::class);
        $this->eventModel    = model(SessionEventModel::class);
        $this->pricingEngine = new PricingEngine();
    }

    public function getLiveData(int $sessionId): ?array
    {
        $session = $this->sessionModel->find($sessionId);
        if (! $session || ! in_array($session['status'], ['active', 'starting'])) {
            return null;
        }

        // Poll provider for current status
        try {
            $adapter = ProviderFactory::getForProvider($session['provider_id']);
            $providerStatus = $adapter->getSessionStatus($session['external_session_id']);
        } catch (\Exception $e) {
            $providerStatus = null;
        }

        $energyKwh = $providerStatus['energy_kwh'] ?? (float) $session['energy_kwh'];
        $durationS = $providerStatus['duration_s'] ?? (int) $session['duration_seconds'];
        $powerKw   = $providerStatus['power_kw'] ?? 0;

        // Calculate live costs
        $snapshot = $this->snapshotModel->find($session['pricing_snapshot_id']);
        $liveCosts = [];
        if ($snapshot) {
            $liveCosts = $this->pricingEngine->calculateSessionCost(
                array_merge($session, [
                    'energy_kwh'       => $energyKwh,
                    'duration_seconds' => $durationS,
                ]),
                $snapshot
            );
        }

        // Update session with latest data
        $this->sessionModel->update($sessionId, [
            'energy_kwh'          => $energyKwh,
            'duration_seconds'    => $durationS,
            'last_live_update_at' => date('Y-m-d H:i:s'),
        ]);

        // Log event
        $totalCost = $liveCosts['total_price_cent'] ?? 0;
        $this->eventModel->logEvent($sessionId, 'live_update', [
            'power_kw' => $powerKw,
        ], $energyKwh, $totalCost);

        return [
            'session_id'      => $sessionId,
            'status'          => $session['status'],
            'energy_kwh'      => round($energyKwh, 4),
            'duration_seconds' => $durationS,
            'power_kw'        => round($powerKw, 2),
            'costs'           => $liveCosts,
            'started_at'      => $session['started_at'],
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
    }
}
