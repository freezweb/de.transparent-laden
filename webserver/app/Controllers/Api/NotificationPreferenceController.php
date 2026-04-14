<?php

namespace App\Controllers\Api;

use App\Models\NotificationPreferenceModel;

class NotificationPreferenceController extends ApiBaseController
{
    private NotificationPreferenceModel $prefModel;

    public function __construct()
    {
        $this->prefModel = model(NotificationPreferenceModel::class);
    }

    public function index()
    {
        $prefs = $this->prefModel->getForUser($this->userId);

        $eventTypes = [
            'session_started',
            'session_completed',
            'session_failed',
            'cost_threshold',
            'invoice_created',
            'subscription_expiring',
            'subscription_cancelled',
        ];

        $result = [];
        foreach ($eventTypes as $type) {
            $found = false;
            foreach ($prefs as $p) {
                if ($p['event_type'] === $type) {
                    $result[] = [
                        'event_type' => $type,
                        'enabled'    => (bool) $p['enabled'],
                    ];
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $result[] = [
                    'event_type' => $type,
                    'enabled'    => true,
                ];
            }
        }

        return $this->respond(['preferences' => $result]);
    }

    public function update()
    {
        $data = $this->request->getJSON(true);

        if (! isset($data['preferences']) || ! is_array($data['preferences'])) {
            return $this->failValidationErrors(['preferences' => 'Preferences array is required']);
        }

        $validEventTypes = [
            'session_started', 'session_completed', 'session_failed',
            'cost_threshold', 'invoice_created',
            'subscription_expiring', 'subscription_cancelled',
        ];

        foreach ($data['preferences'] as $pref) {
            if (! isset($pref['event_type']) || ! in_array($pref['event_type'], $validEventTypes, true)) {
                return $this->failValidationErrors(['event_type' => 'Invalid event type']);
            }
            if (! array_key_exists('enabled', $pref)) {
                return $this->failValidationErrors(['enabled' => 'Enabled field is required']);
            }
            $this->prefModel->setPreference(
                $this->userId,
                $pref['event_type'],
                (bool) $pref['enabled']
            );
        }

        return $this->respond(['message' => 'Preferences updated']);
    }
}
