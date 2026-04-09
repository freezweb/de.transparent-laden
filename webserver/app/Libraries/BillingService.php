<?php

namespace App\Libraries;

use App\Models\InvoiceModel;
use App\Models\ChargingSessionModel;
use App\Models\UserModel;
use App\Models\AuditLogModel;

class BillingService
{
    private InvoiceModel $invoiceModel;
    private ChargingSessionModel $sessionModel;
    private UserModel $userModel;
    private AuditLogModel $auditLog;
    private LexwareAdapter $lexware;

    public function __construct()
    {
        $this->invoiceModel = model(InvoiceModel::class);
        $this->sessionModel = model(ChargingSessionModel::class);
        $this->userModel    = model(UserModel::class);
        $this->auditLog     = model(AuditLogModel::class);
        $this->lexware      = new LexwareAdapter();
    }

    public function createChargingInvoice(int $sessionId): ?array
    {
        $session = $this->sessionModel->find($sessionId);
        if (! $session || $session['status'] !== 'completed') {
            return null;
        }

        $user = $this->userModel->find($session['user_id']);
        $taxRate = 19.00;
        $grossAmount = $session['total_price_cent'];
        $netAmount = (int) round($grossAmount / (1 + $taxRate / 100));
        $taxAmount = $grossAmount - $netAmount;

        $lineItems = [
            [
                'description' => 'Ladevorgang',
                'energy_kwh'  => $session['energy_kwh'],
                'details'     => [
                    'energy_cost'   => $session['energy_cost_cent'],
                    'time_cost'     => $session['time_cost_cent'],
                    'blocking_cost' => $session['blocking_cost_cent'],
                    'start_fee'     => $session['start_fee_cent'],
                    'roaming_fee'   => $session['roaming_fee_cent'],
                    'platform_fee'  => $session['platform_fee_cent'],
                    'payment_fee'   => $session['payment_fee_cent'],
                ],
                'amount_cent' => $grossAmount,
            ],
        ];

        $invoiceId = $this->invoiceModel->insert([
            'user_id'           => $session['user_id'],
            'session_id'        => $sessionId,
            'invoice_number'    => $this->invoiceModel->getNextInvoiceNumber(),
            'invoice_type'      => 'charging',
            'net_amount_cent'   => $netAmount,
            'tax_amount_cent'   => $taxAmount,
            'gross_amount_cent' => $grossAmount,
            'tax_rate'          => $taxRate,
            'line_items_json'   => json_encode($lineItems),
            'issued_at'         => date('Y-m-d H:i:s'),
        ]);

        $this->auditLog->log('invoice', $invoiceId, 'created', 'system', null);

        // Queue Lexware sync
        $this->syncToLexware($invoiceId);

        return $this->invoiceModel->find($invoiceId);
    }

    public function syncToLexware(int $invoiceId): bool
    {
        $invoice = $this->invoiceModel->find($invoiceId);
        if (! $invoice) {
            return false;
        }

        try {
            $voucherId = $this->lexware->createVoucher($invoice);

            $this->invoiceModel->update($invoiceId, [
                'lexware_voucher_id' => $voucherId,
                'lexware_status'     => 'synced',
                'lexware_synced_at'  => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->invoiceModel->update($invoiceId, [
                'lexware_status' => 'failed',
                'lexware_error'  => $e->getMessage(),
            ]);
            log_message('error', 'Lexware sync failed for invoice ' . $invoiceId . ': ' . $e->getMessage());
            return false;
        }
    }

    public function retryPendingSync(): int
    {
        $pending = $this->invoiceModel->getPendingLexwareSync();
        $synced = 0;

        foreach ($pending as $invoice) {
            if ($this->syncToLexware($invoice['id'])) {
                $synced++;
            }
        }

        return $synced;
    }
}
