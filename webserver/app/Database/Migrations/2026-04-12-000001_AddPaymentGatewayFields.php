<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentGatewayFields extends Migration
{
    public function up()
    {
        // stripe_customer_id zur users-Tabelle hinzufügen
        if ($this->db->tableExists('users')) {
            $this->forge->addColumn('users', [
                'stripe_customer_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'email',
                ],
            ]);
        }

        // payment_gateway + payment_gateway_ref zu charging_sessions hinzufügen
        if ($this->db->tableExists('charging_sessions')) {
            $this->forge->addColumn('charging_sessions', [
                'payment_gateway' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'payment_method_id',
                ],
                'payment_gateway_ref' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'payment_gateway',
                ],
                'payment_captured' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'payment_gateway_ref',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('stripe_customer_id', 'users')) {
            $this->forge->dropColumn('users', 'stripe_customer_id');
        }

        if ($this->db->tableExists('charging_sessions')) {
            if ($this->db->fieldExists('payment_gateway', 'charging_sessions')) {
                $this->forge->dropColumn('charging_sessions', 'payment_gateway');
            }
            if ($this->db->fieldExists('payment_gateway_ref', 'charging_sessions')) {
                $this->forge->dropColumn('charging_sessions', 'payment_gateway_ref');
            }
            if ($this->db->fieldExists('payment_captured', 'charging_sessions')) {
                $this->forge->dropColumn('charging_sessions', 'payment_captured');
            }
        }
    }
}
