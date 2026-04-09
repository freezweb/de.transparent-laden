<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\NotificationService;

class NotificationsProcess extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'notifications:process-queue';
    protected $description = 'Process pending notification queue and send via FCM';

    public function run(array $params)
    {
        $notificationService = new NotificationService();

        CLI::write('Processing notification queue...', 'yellow');

        try {
            $result = $notificationService->processBatch(50);
            CLI::write("Processed: {$result['processed']}, Sent: {$result['sent']}, Failed: {$result['failed']}", 'green');
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
        }
    }
}
