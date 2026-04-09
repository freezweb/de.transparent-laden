<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\BillingService;

class BillingRetryPending extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'billing:retry-pending';
    protected $description = 'Retry pending Lexware synchronization for invoices';

    public function run(array $params)
    {
        $billingService = new BillingService();

        CLI::write('Retrying pending Lexware sync...', 'yellow');

        try {
            $result = $billingService->retryPendingSync();
            CLI::write("Processed: {$result['processed']}, Success: {$result['success']}, Failed: {$result['failed']}", 'green');
        } catch (\Exception $e) {
            CLI::error('Error: ' . $e->getMessage());
        }
    }
}
