<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'           => ['type' => 'INT', 'unsigned' => true],
            'session_id'        => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'invoice_number'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'invoice_type'      => ['type' => 'ENUM', 'constraint' => ['charging', 'subscription', 'credit_note'], 'default' => 'charging'],
            'net_amount_cent'   => ['type' => 'INT', 'default' => 0],
            'tax_amount_cent'   => ['type' => 'INT', 'default' => 0],
            'gross_amount_cent' => ['type' => 'INT', 'default' => 0],
            'tax_rate'          => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 19.00],
            'line_items_json'   => ['type' => 'JSON', 'null' => true],
            'lexware_voucher_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'lexware_status'    => ['type' => 'ENUM', 'constraint' => ['pending', 'synced', 'failed', 'skipped'], 'default' => 'pending'],
            'lexware_synced_at' => ['type' => 'DATETIME', 'null' => true],
            'lexware_error'     => ['type' => 'TEXT', 'null' => true],
            'pdf_path'          => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'issued_at'         => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('invoice_number');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('session_id', 'charging_sessions', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addKey('user_id');
        $this->forge->createTable('invoices');
    }

    public function down()
    {
        $this->forge->dropTable('invoices');
    }
}
