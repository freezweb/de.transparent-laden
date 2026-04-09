<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'session_id', 'invoice_number', 'invoice_type',
        'net_amount_cent', 'tax_amount_cent', 'gross_amount_cent', 'tax_rate',
        'line_items_json', 'lexware_voucher_id', 'lexware_status',
        'lexware_synced_at', 'lexware_error', 'pdf_path', 'issued_at',
    ];

    public function getForUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        return $this->where('user_id', $userId)
                     ->orderBy('issued_at', 'DESC')
                     ->paginate($perPage, 'default', $page);
    }

    public function getNextInvoiceNumber(): string
    {
        $year = date('Y');
        $last = $this->like('invoice_number', "EL-{$year}-", 'after')
                      ->orderBy('id', 'DESC')
                      ->first();

        if ($last) {
            $parts = explode('-', $last['invoice_number']);
            $seq = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('EL-%s-%06d', $year, $seq);
    }

    public function getPendingLexwareSync(): array
    {
        return $this->where('lexware_status', 'pending')
                     ->orderBy('created_at', 'ASC')
                     ->findAll();
    }
}
