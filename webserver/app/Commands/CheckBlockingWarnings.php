<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ChargingSessionModel;
use App\Libraries\NotificationService;
use App\Models\SystemConfigModel;

class CheckBlockingWarnings extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'charging:check-blocking-warnings';
    protected $description = 'Send warnings for sessions that are about to incur blocking fees';

    public function run(array $params)
    {
        $sessionModel        = model(ChargingSessionModel::class);
        $configModel         = model(SystemConfigModel::class);
        $notificationService = new NotificationService();

        $blockingThresholdMinutes = (int) ($configModel->getValue('blocking_warning_minutes') ?? 10);

        $activeSessions = $sessionModel
            ->where('status', 'active')
            ->where('energy_kwh >', 0)
            ->findAll();

        CLI::write('Checking ' . count($activeSessions) . ' active sessions for blocking warnings', 'yellow');

        $warned = 0;

        foreach ($activeSessions as $session) {
            if (empty($session['last_provider_update_at'])) {
                continue;
            }

            $lastUpdate = strtotime($session['last_provider_update_at']);
            $now        = time();
            $idleMinutes = ($now - $lastUpdate) / 60;

            $sessionDuration = ($now - strtotime($session['started_at'])) / 60;

            if ($sessionDuration > 240 && $idleMinutes > $blockingThresholdMinutes) {
                try {
                    $notificationService->enqueue(
                        (int) $session['user_id'],
                        'cost_threshold',
                        'Blockiergebühr-Warnung',
                        'Ihr Ladevorgang scheint abgeschlossen zu sein. Bitte beenden Sie die Session, um Blockiergebühren zu vermeiden.',
                        ['session_id' => $session['id']]
                    );
                    $warned++;
                    CLI::write("  -> Warning sent for session #{$session['id']}", 'yellow');
                } catch (\Exception $e) {
                    CLI::error("  -> Error for session #{$session['id']}: " . $e->getMessage());
                }
            }
        }

        CLI::write("Done. Warnings sent: {$warned}", 'green');
    }
}
