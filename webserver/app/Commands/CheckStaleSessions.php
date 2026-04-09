<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ChargingSessionModel;
use App\Libraries\NotificationService;

class CheckStaleSessions extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'charging:check-stale-sessions';
    protected $description = 'Check for stale charging sessions and send notifications';

    public function run(array $params)
    {
        $sessionModel       = model(ChargingSessionModel::class);
        $notificationService = new NotificationService();

        $staleSessions = $sessionModel->getStale(120);

        CLI::write('Found ' . count($staleSessions) . ' stale sessions', 'yellow');

        foreach ($staleSessions as $session) {
            CLI::write("Session #{$session['id']} - User #{$session['user_id']} - Status: {$session['status']}", 'light_gray');

            try {
                $notificationService->enqueue(
                    (int) $session['user_id'],
                    'session_failed',
                    'Charging Session Warning',
                    "Your charging session #{$session['id']} appears to be stale. Please check.",
                    ['session_id' => $session['id']]
                );
            } catch (\Exception $e) {
                CLI::error("  -> Notification error: {$e->getMessage()}");
            }
        }

        CLI::write('Done.', 'green');
    }
}
