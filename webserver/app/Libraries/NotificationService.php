<?php

namespace App\Libraries;

use App\Models\NotificationQueueModel;
use App\Models\NotificationPreferenceModel;
use App\Models\UserDeviceModel;

class NotificationService
{
    private NotificationQueueModel $queueModel;
    private NotificationPreferenceModel $prefModel;
    private UserDeviceModel $deviceModel;

    public function __construct()
    {
        $this->queueModel  = model(NotificationQueueModel::class);
        $this->prefModel   = model(NotificationPreferenceModel::class);
        $this->deviceModel = model(UserDeviceModel::class);
    }

    public function enqueue(int $userId, string $eventType, array $payload, ?int $sessionId = null): ?int
    {
        // Check user preferences
        if (! $this->prefModel->isEnabled($userId, $eventType)) {
            return null;
        }

        // Get active devices
        $devices = $this->deviceModel->getActiveForUser($userId);
        if (empty($devices)) {
            return null;
        }

        $deviceIds = array_column($devices, 'id');

        return $this->queueModel->insert([
            'user_id'                => $userId,
            'session_id'             => $sessionId,
            'event_type'             => $eventType,
            'payload_json'           => json_encode($payload),
            'target_device_ids_json' => json_encode($deviceIds),
            'next_attempt_at'        => date('Y-m-d H:i:s'),
        ]);
    }

    public function processBatch(int $batchSize = 50): array
    {
        $pending = $this->queueModel->getPendingBatch($batchSize);
        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($pending as $item) {
            $this->queueModel->markProcessing($item['id']);

            try {
                $deviceIds = json_decode($item['target_device_ids_json'], true) ?? [];
                $payload = json_decode($item['payload_json'], true) ?? [];

                $sent = $this->sendToDevices($deviceIds, $item['event_type'], $payload);

                if ($sent > 0) {
                    $this->queueModel->markSent($item['id']);
                    $results['sent']++;
                } else {
                    $this->queueModel->markFailed($item['id'], 'No devices received notification');
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $this->queueModel->markFailed($item['id'], $e->getMessage());
                $results['failed']++;
            }
        }

        return $results;
    }

    private function sendToDevices(array $deviceIds, string $eventType, array $payload): int
    {
        $sent = 0;
        $fcmKey = env('fcm.serverKey', '');

        if (empty($fcmKey)) {
            log_message('warning', 'FCM server key not configured');
            return 0;
        }

        foreach ($deviceIds as $deviceId) {
            $device = $this->deviceModel->find($deviceId);
            if (! $device || ! $device['is_active']) {
                continue;
            }

            $notification = $this->buildNotification($eventType, $payload);

            $fcmPayload = [
                'to'           => $device['push_token'],
                'notification' => $notification,
                'data'         => array_merge($payload, ['event_type' => $eventType]),
            ];

            try {
                $client = service('curlrequest');
                $response = $client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
                    'headers' => [
                        'Authorization' => 'key=' . $fcmKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => $fcmPayload,
                ]);

                if ($response->getStatusCode() === 200) {
                    $sent++;
                }
            } catch (\Exception $e) {
                log_message('error', "FCM send failed for device {$deviceId}: " . $e->getMessage());
            }
        }

        return $sent;
    }

    private function buildNotification(string $eventType, array $payload): array
    {
        return match ($eventType) {
            'charging_started' => [
                'title' => 'Ladevorgang gestartet',
                'body'  => 'Dein Ladevorgang wurde erfolgreich gestartet.',
            ],
            'charging_completed' => [
                'title' => 'Ladevorgang abgeschlossen',
                'body'  => sprintf(
                    'Geladen: %.1f kWh – Kosten: %.2f €',
                    $payload['energy_kwh'] ?? 0,
                    ($payload['total_price_cent'] ?? 0) / 100
                ),
            ],
            'charging_failed' => [
                'title' => 'Ladevorgang fehlgeschlagen',
                'body'  => 'Es gab ein Problem mit deinem Ladevorgang.',
            ],
            'blocking_warning' => [
                'title' => 'Blockiergebühr aktiv',
                'body'  => 'Dein Fahrzeug blockiert den Ladepunkt. Bitte umparken.',
            ],
            default => [
                'title' => 'Einfach Laden',
                'body'  => 'Neue Benachrichtigung',
            ],
        };
    }
}
