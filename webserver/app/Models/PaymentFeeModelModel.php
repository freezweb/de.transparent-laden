<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentFeeModelModel extends Model
{
    protected $table            = 'payment_fee_models';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $createdField     = 'created_at';

    protected $allowedFields = [
        'name', 'payment_type', 'fixed_fee_cent', 'percentage_fee',
        'min_fee_cent', 'max_fee_cent', 'is_active', 'valid_from', 'valid_until',
    ];

    public function getActiveForType(string $paymentType): ?array
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('payment_type', $paymentType)
                     ->where('is_active', 1)
                     ->where('valid_from <=', $now)
                     ->groupStart()
                         ->where('valid_until IS NULL')
                         ->orWhere('valid_until >', $now)
                     ->groupEnd()
                     ->first();
    }

    public function calculateFee(array $feeModel, int $amountCent): int
    {
        $fixedFee = $feeModel['fixed_fee_cent'];
        $percentageFee = (int) round($amountCent * ($feeModel['percentage_fee'] / 100));
        $totalFee = $fixedFee + $percentageFee;

        if ($feeModel['min_fee_cent'] > 0) {
            $totalFee = max($totalFee, $feeModel['min_fee_cent']);
        }

        if ($feeModel['max_fee_cent'] > 0) {
            $totalFee = min($totalFee, $feeModel['max_fee_cent']);
        }

        return $totalFee;
    }
}
