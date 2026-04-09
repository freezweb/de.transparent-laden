<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserSubscriptionModel;
use App\Models\InvoiceModel;
use App\Libraries\BillingService;

class BillingSubscriptionInvoices extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'billing:subscription-invoices';
    protected $description = 'Generate monthly subscription invoices for active subscriptions';

    public function run(array $params)
    {
        $subModel    = model(UserSubscriptionModel::class);
        $invoiceModel = model(InvoiceModel::class);
        $db           = \Config\Database::connect();

        $activeSubs = $subModel->where('status', 'active')->findAll();

        CLI::write('Found ' . count($activeSubs) . ' active subscriptions', 'yellow');

        $created = 0;
        $skipped = 0;

        foreach ($activeSubs as $sub) {
            $lastInvoice = $invoiceModel
                ->where('user_id', $sub['user_id'])
                ->where('type', 'subscription')
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($lastInvoice) {
                $lastDate = strtotime($lastInvoice['created_at']);
                $interval = $sub['billing_cycle'] === 'yearly' ? strtotime('-1 year') : strtotime('-1 month');

                if ($lastDate > $interval) {
                    $skipped++;
                    continue;
                }
            }

            try {
                $planVersion = $db->table('subscription_plan_versions')
                    ->where('id', $sub['plan_version_id'])
                    ->get()->getRowArray();

                $plan = $db->table('subscription_plans')
                    ->where('id', $planVersion['plan_id'])
                    ->get()->getRowArray();

                $amount = $sub['billing_cycle'] === 'yearly'
                    ? (int) $plan['price_yearly_cent']
                    : (int) $plan['price_monthly_cent'];

                if ($amount <= 0) {
                    $skipped++;
                    continue;
                }

                $vatRate     = 19;
                $netCent     = (int) round($amount / (1 + $vatRate / 100));
                $vatCent     = $amount - $netCent;

                $invoiceNumber = $invoiceModel->getNextInvoiceNumber();

                $invoiceModel->insert([
                    'user_id'          => $sub['user_id'],
                    'session_id'       => null,
                    'invoice_number'   => $invoiceNumber,
                    'type'             => 'subscription',
                    'total_net_cent'   => $netCent,
                    'vat_rate'         => $vatRate,
                    'vat_cent'         => $vatCent,
                    'total_gross_cent' => $amount,
                    'line_items_json'  => json_encode([
                        [
                            'description' => "Abo: {$plan['name']} ({$sub['billing_cycle']})",
                            'amount_cent' => $amount,
                        ],
                    ]),
                    'status'           => 'created',
                ]);

                $created++;
                CLI::write("  -> Invoice created for user #{$sub['user_id']}: {$invoiceNumber}", 'green');
            } catch (\Exception $e) {
                CLI::error("  -> Error for user #{$sub['user_id']}: " . $e->getMessage());
            }
        }

        CLI::write("Done. Created: {$created}, Skipped: {$skipped}", 'green');
    }
}
