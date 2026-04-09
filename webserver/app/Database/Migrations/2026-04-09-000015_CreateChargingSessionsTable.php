<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChargingSessionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                        => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'                   => ['type' => 'INT', 'unsigned' => true],
            'connector_id'              => ['type' => 'INT', 'unsigned' => true],
            'provider_id'               => ['type' => 'INT', 'unsigned' => true],
            'payment_method_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'pricing_snapshot_id'       => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'status'                    => ['type' => 'ENUM', 'constraint' => ['pending', 'starting', 'active', 'stopping', 'completed', 'failed', 'cancelled'], 'default' => 'pending'],
            'external_session_id'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'started_at'                => ['type' => 'DATETIME', 'null' => true],
            'stopped_at'                => ['type' => 'DATETIME', 'null' => true],
            'energy_kwh'                => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0],
            'duration_seconds'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'blocking_duration_seconds' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'energy_cost_cent'          => ['type' => 'INT', 'default' => 0],
            'time_cost_cent'            => ['type' => 'INT', 'default' => 0],
            'blocking_cost_cent'        => ['type' => 'INT', 'default' => 0],
            'start_fee_cent'            => ['type' => 'INT', 'default' => 0],
            'roaming_fee_cent'          => ['type' => 'INT', 'default' => 0],
            'platform_fee_cent'         => ['type' => 'INT', 'default' => 0],
            'payment_fee_cent'          => ['type' => 'INT', 'default' => 0],
            'total_price_cent'          => ['type' => 'INT', 'default' => 0],
            'last_live_update_at'       => ['type' => 'DATETIME', 'null' => true],
            'recovery_attempts'         => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'last_recovery_at'          => ['type' => 'DATETIME', 'null' => true],
            'failure_reason'            => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'                => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('connector_id', 'connectors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('provider_id', 'providers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('pricing_snapshot_id', 'pricing_snapshots', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->createTable('charging_sessions');
    }

    public function down()
    {
        $this->forge->dropTable('charging_sessions');
    }
}
