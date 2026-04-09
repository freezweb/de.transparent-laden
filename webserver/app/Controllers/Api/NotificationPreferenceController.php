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
        $rules = [
            'preferences'              => 'required',
            'preferences.*.event_type' => 'required|in_list[session_started,session_completed,session_failed,cost_threshold,invoice_created,subscription_expiring,subscription_cancelled]',
            'preferences.*.enabled'    => 'required|in_list[0,1,true,false]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        foreach ($data['preferences'] as $pref) {
            $this->prefModel->setPreference(
                $this->userId,
                $pref['event_type'],
                (bool) $pref['enabled']
            );
        }

        return $this->respond(['message' => 'Preferences updated']);
    }
}
