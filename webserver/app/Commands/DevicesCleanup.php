<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserDeviceModel;

class DevicesCleanup extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'devices:cleanup';
    protected $description = 'Remove inactive devices older than 90 days';

    public function run(array $params)
    {
        $deviceModel = model(UserDeviceModel::class);

        CLI::write('Cleaning up inactive devices...', 'yellow');

        $removed = $deviceModel->cleanupInactive(90);

        CLI::write("Removed {$removed} inactive devices.", 'green');
    }
}
