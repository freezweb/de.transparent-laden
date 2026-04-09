<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\NotificationQueueModel;

class NotificationsCleanup extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'notifications:cleanup-sent';
    protected $description = 'Clean up sent notifications older than 30 days';

    public function run(array $params)
    {
        $queueModel = model(NotificationQueueModel::class);

        CLI::write('Cleaning up old sent notifications...', 'yellow');

        $removed = $queueModel->cleanupSent(30);

        CLI::write("Removed {$removed} old notifications.", 'green');
    }
}
