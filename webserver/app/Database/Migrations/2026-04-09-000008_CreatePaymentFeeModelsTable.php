<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentFeeModelsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'payment_type'    => ['type' => 'VARCHAR', 'constraint' => 30],
            'fixed_fee_cent'  => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'percentage_fee'  => ['type' => 'DECIMAL', 'constraint' => '6,4', 'default' => 0],
            'min_fee_cent'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'max_fee_cent'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'valid_from'      => ['type' => 'DATETIME'],
            'valid_until'     => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('payment_fee_models');
    }

    public function down()
    {
        $this->forge->dropTable('payment_fee_models');
    }
}
