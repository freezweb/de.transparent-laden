<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSessionEventsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_id'          => ['type' => 'BIGINT', 'unsigned' => true],
            'event_type'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'event_data_json'     => ['type' => 'JSON', 'null' => true],
            'energy_kwh_at_event' => ['type' => 'DECIMAL', 'constraint' => '10,4', 'default' => 0],
            'live_cost_cent'      => ['type' => 'INT', 'default' => 0],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('session_id', 'charging_sessions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('session_id');
        $this->forge->createTable('session_events');
    }

    public function down()
    {
        $this->forge->dropTable('session_events');
    }
}
