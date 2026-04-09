<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePricingSnapshotsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'connector_id'                   => ['type' => 'INT', 'unsigned' => true],
            'provider_id'                    => ['type' => 'INT', 'unsigned' => true],
            'snapshot_time'                  => ['type' => 'DATETIME'],
            // Structured tariff (raw from provider)
            'structured_tariff_json'         => ['type' => 'JSON', 'null' => true],
            // Resolved tariff components
            'energy_price_per_kwh_cent'      => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0],
            'time_price_per_min_cent'        => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0],
            'blocking_fee_per_min_cent'      => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0],
            'blocking_free_minutes'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'start_fee_cent'                 => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'min_billing_amount_cent'        => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            // Roaming fee
            'roaming_fee_cent'               => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            // Platform fee
            'platform_fee_cent'              => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'platform_fee_reduction_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'platform_fee_effective_cent'    => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            // Payment fee
            'payment_fee_cent'               => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'payment_fee_model_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            // Total
            'estimated_total_per_kwh_cent'   => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0],
            // Transparency JSON for display
            'transparency_json'              => ['type' => 'JSON', 'null' => true],
            'created_at'                     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('connector_id', 'connectors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('provider_id', 'providers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('connector_id');
        $this->forge->addKey('snapshot_time');
        $this->forge->createTable('pricing_snapshots');
    }

    public function down()
    {
        $this->forge->dropTable('pricing_snapshots');
    }
}
