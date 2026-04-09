<?php

namespace App\Libraries;

use App\Libraries\Provider\ProviderFactory;
use App\Models\ChargingSessionModel;
use App\Models\ConnectorModel;
use App\Models\PricingSnapshotModel;
use App\Models\SessionEventModel;
use App\Models\AuditLogModel;

class ChargingService
{
    private ChargingSessionModel $sessionModel;
    private ConnectorModel $connectorModel;
    private PricingSnapshotModel $snapshotModel;
    private SessionEventModel $eventModel;
    private PricingEngine $pricingEngine;
    private NotificationService $notificationService;
    private AuditLogModel $auditLog;

    public function __construct()
    {
        $this->sessionModel        = model(ChargingSessionModel::class);
        $this->connectorModel      = model(ConnectorModel::class);
        $this->snapshotModel       = model(PricingSnapshotModel::class);
        $this->eventModel          = model(SessionEventModel::class);
        $this->pricingEngine       = new PricingEngine();
        $this->notificationService = new NotificationService();
        $this->auditLog            = model(AuditLogModel::class);
    }

    public function startSession(int $userId, int $connectorId, ?int $paymentMethodId = null): array
    {
        // Check no active session
        $active = $this->sessionModel->getActiveForUser($userId);
        if ($active) {
            throw new \RuntimeException('Active session already exists');
        }

        $connector = $this->connectorModel->find($connectorId);
        if (! $connector) {
            throw new \RuntimeException('Connector not found');
        }

        // Create session in pending state
        $sessionId = $this->sessionModel->insert([
            'user_id'            => $userId,
            'connector_id'       => $connectorId,
            'provider_id'        => $connector['charge_point_id'], // will be set from charge_point
            'payment_method_id'  => $paymentMethodId,
            'status'             => 'pending',
        ]);

        // Resolve provider
        $chargePointModel = model(\App\Models\ChargePointModel::class);
        $chargePoint = $chargePointModel->find($connector['charge_point_id']);
        $providerId = $chargePoint['provider_id'];

        $this->sessionModel->update($sessionId, ['provider_id' => $providerId]);

        // Get pricing snapshot
        $snapshot = $this->snapshotModel->getLatestForConnector($connectorId);
        if ($snapshot) {
            $this->sessionModel->update($sessionId, ['pricing_snapshot_id' => $snapshot['id']]);
        }

        // Start via provider adapter
        try {
            $this->sessionModel->update($sessionId, ['status' => 'starting']);
            $adapter = ProviderFactory::getForProvider($providerId);
            $externalId = $adapter->startSession($connector['external_id'], 'user-' . $userId);

            $this->sessionModel->update($sessionId, [
                'status'              => 'active',
                'external_session_id' => $externalId,
                'started_at'          => date('Y-m-d H:i:s'),
                'last_live_update_at' => date('Y-m-d H:i:s'),
            ]);

            $this->eventModel->logEvent($sessionId, 'session_started', ['external_id' => $externalId]);
            $this->notificationService->enqueue($userId, 'charging_started', ['session_id' => $sessionId], $sessionId);

        } catch (\Exception $e) {
            $this->sessionModel->update($sessionId, [
                'status'         => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);
            $this->eventModel->logEvent($sessionId, 'start_failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        $this->auditLog->log('session', $sessionId, 'start', 'user', $userId);

        return $this->sessionModel->find($sessionId);
    }

    public function stopSession(int $userId, int $sessionId): array
    {
        $session = $this->sessionModel->find($sessionId);
        if (! $session || $session['user_id'] !== $userId) {
            throw new \RuntimeException('Session not found');
        }

        if (! in_array($session['status'], ['active', 'starting'])) {
            throw new \RuntimeException('Session cannot be stopped in current state');
        }

        $this->sessionModel->update($sessionId, ['status' => 'stopping']);

        try {
            $adapter = ProviderFactory::getForProvider($session['provider_id']);
            $adapter->stopSession($session['external_session_id']);

            // Get final status from provider
            $status = $adapter->getSessionStatus($session['external_session_id']);

            $snapshot = $this->snapshotModel->find($session['pricing_snapshot_id']);
            $costs = $snapshot ? $this->pricingEngine->calculateSessionCost(
                array_merge($session, [
                    'energy_kwh'                => $status['energy_kwh'] ?? $session['energy_kwh'],
                    'duration_seconds'          => $status['duration_s'] ?? $session['duration_seconds'],
                    'blocking_duration_seconds' => $session['blocking_duration_seconds'],
                ]),
                $snapshot
            ) : [];

            $updateData = [
                'status'     => 'completed',
                'stopped_at' => date('Y-m-d H:i:s'),
                'energy_kwh' => $status['energy_kwh'] ?? $session['energy_kwh'],
                'duration_seconds' => $status['duration_s'] ?? $session['duration_seconds'],
            ];

            $this->sessionModel->update($sessionId, array_merge($updateData, $costs));
            $this->eventModel->logEvent($sessionId, 'session_completed', $costs, $status['energy_kwh'] ?? 0, $costs['total_price_cent'] ?? 0);

            $this->notificationService->enqueue($userId, 'charging_completed', [
                'session_id'      => $sessionId,
                'energy_kwh'      => $status['energy_kwh'] ?? 0,
                'total_price_cent' => $costs['total_price_cent'] ?? 0,
            ], $sessionId);

        } catch (\Exception $e) {
            $this->sessionModel->update($sessionId, [
                'status'         => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);
            $this->eventModel->logEvent($sessionId, 'stop_failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        $this->auditLog->log('session', $sessionId, 'stop', 'user', $userId);
        return $this->sessionModel->find($sessionId);
    }

    public function getLiveStatus(int $sessionId): ?array
    {
        $session = $this->sessionModel->find($sessionId);
        if (! $session || ! in_array($session['status'], ['active', 'starting'])) {
            return null;
        }

        try {
            $adapter = ProviderFactory::getForProvider($session['provider_id']);
            $status = $adapter->getSessionStatus($session['external_session_id']);

            if ($status) {
                $snapshot = $this->snapshotModel->find($session['pricing_snapshot_id']);
                $liveCosts = $snapshot ? $this->pricingEngine->calculateSessionCost(
                    array_merge($session, [
                        'energy_kwh'       => $status['energy_kwh'],
                        'duration_seconds' => $status['duration_s'],
                    ]),
                    $snapshot
                ) : [];

                $this->sessionModel->update($sessionId, [
                    'energy_kwh'          => $status['energy_kwh'],
                    'duration_seconds'    => $status['duration_s'],
                    'last_live_update_at' => date('Y-m-d H:i:s'),
                ]);

                return array_merge($status, ['live_costs' => $liveCosts]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Live status error: ' . $e->getMessage());
        }

        return null;
    }

    public function forceCompleteSession(int $sessionId): void
    {
        $session = $this->sessionModel->find($sessionId);
        if (! $session) {
            throw new \RuntimeException('Session not found');
        }

        $snapshot = $session['pricing_snapshot_id']
            ? $this->snapshotModel->find($session['pricing_snapshot_id'])
            : null;

        $costs = [];
        if ($snapshot && $session['energy_kwh'] > 0) {
            $costs = $this->pricingEngine->calculateSessionCost($session, $snapshot);
        }

        $this->sessionModel->update($sessionId, [
            'status'           => 'completed',
            'ended_at'         => date('Y-m-d H:i:s'),
            'total_price_cent' => $costs['total_price_cent'] ?? 0,
        ]);

        $this->eventModel->logEvent($sessionId, 'force_completed', [
            'reason' => 'recovery',
        ]);

        try {
            $this->notificationService->enqueue(
                (int) $session['user_id'],
                'session_completed',
                'Ladevorgang abgeschlossen',
                'Ihr Ladevorgang wurde automatisch abgeschlossen.',
                ['session_id' => $sessionId]
            );
        } catch (\Exception $e) {
            log_message('error', 'Notification error on force complete: ' . $e->getMessage());
        }
    }
}
