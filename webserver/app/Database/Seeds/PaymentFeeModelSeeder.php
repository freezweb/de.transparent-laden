<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaymentFeeModelSeeder extends Seeder
{
    public function run()
    {
        $feeModels = [
            [
                'name'            => 'Kreditkarte Standard',
                'payment_type'    => 'credit_card',
                'fixed_fee_cent'  => 0,
                'percentage_fee'  => 1.9000,
                'min_fee_cent'    => 0,
                'max_fee_cent'    => null,
                'valid_from'      => date('Y-m-d H:i:s'),
                'valid_until'     => null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'Debitkarte Standard',
                'payment_type'    => 'debit_card',
                'fixed_fee_cent'  => 0,
                'percentage_fee'  => 0.9000,
                'min_fee_cent'    => 0,
                'max_fee_cent'    => null,
                'valid_from'      => date('Y-m-d H:i:s'),
                'valid_until'     => null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'PayPal',
                'payment_type'    => 'paypal',
                'fixed_fee_cent'  => 35,
                'percentage_fee'  => 2.4900,
                'min_fee_cent'    => 0,
                'max_fee_cent'    => null,
                'valid_from'      => date('Y-m-d H:i:s'),
                'valid_until'     => null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'SEPA-Lastschrift',
                'payment_type'    => 'sepa',
                'fixed_fee_cent'  => 20,
                'percentage_fee'  => 0.0000,
                'min_fee_cent'    => 0,
                'max_fee_cent'    => null,
                'valid_from'      => date('Y-m-d H:i:s'),
                'valid_until'     => null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'Apple Pay',
                'payment_type'    => 'apple_pay',
                'fixed_fee_cent'  => 0,
                'percentage_fee'  => 1.5000,
                'min_fee_cent'    => 0,
                'max_fee_cent'    => null,
                'valid_from'      => date('Y-m-d H:i:s'),
                'valid_until'     => null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'Google Pay',
                'payment_type'    => 'google_pay',
                'fixed_fee_cent'  => 0,
                'percentage_fee'  => 1.5000,
                'min_fee_cent'    => 0,
                'max_fee_cent'    => null,
                'valid_from'      => date('Y-m-d H:i:s'),
                'valid_until'     => null,
                'is_active'       => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($feeModels as $model) {
            $this->db->table('payment_fee_models')->insert($model);
        }
    }
}
