<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentMethodsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'             => ['type' => 'INT', 'unsigned' => true],
            'type'                => ['type' => 'ENUM', 'constraint' => ['credit_card', 'debit_card', 'paypal', 'sepa', 'apple_pay', 'google_pay']],
            'label'               => ['type' => 'VARCHAR', 'constraint' => 100],
            'is_default'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'external_reference'  => ['type' => 'TEXT', 'null' => true],
            'fee_model_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['active', 'expired', 'revoked'], 'default' => 'active'],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('fee_model_id', 'payment_fee_models', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addKey('user_id');
        $this->forge->createTable('payment_methods');
    }

    public function down()
    {
        $this->forge->dropTable('payment_methods');
    }
}
