<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ChargingSessionModel;
use App\Libraries\ChargingService;

class RecoverStuckSessions extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'charging:recover-stuck-sessions';
    protected $description = 'Attempt recovery of stuck charging sessions';

    public function run(array $params)
    {
        $sessionModel   = model(ChargingSessionModel::class);
        $chargingService = new ChargingService();

        $stuckSessions = $sessionModel->getStuckForRecovery();

        CLI::write('Found ' . count($stuckSessions) . ' stuck sessions for recovery', 'yellow');

        foreach ($stuckSessions as $session) {
            CLI::write("Recovering session #{$session['id']} (attempts: {$session['recovery_attempts']})", 'light_gray');

            try {
                $sessionModel->update($session['id'], [
                    'recovery_attempts'   => $session['recovery_attempts'] + 1,
                    'last_recovery_at'    => date('Y-m-d H:i:s'),
                ]);

                if ($session['status'] === 'starting') {
                    $sessionModel->update($session['id'], ['status' => 'failed']);
                    CLI::write("  -> Marked as failed (stuck in starting)", 'red');
                } elseif ($session['status'] === 'stopping') {
                    $chargingService->forceCompleteSession((int) $session['id']);
                    CLI::write("  -> Force completed", 'green');
                } elseif ($session['status'] === 'active' && $session['recovery_attempts'] >= 3) {
                    $sessionModel->update($session['id'], ['status' => 'failed']);
                    CLI::write("  -> Marked as failed after 3 recovery attempts", 'red');
                }
            } catch (\Exception $e) {
                CLI::error("  -> Recovery error: {$e->getMessage()}");
            }
        }

        CLI::write('Recovery complete.', 'green');
    }
}
