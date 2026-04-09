<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConnectorsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'charge_point_id'       => ['type' => 'INT', 'unsigned' => true],
            'external_id'           => ['type' => 'VARCHAR', 'constraint' => 200],
            'connector_type'        => ['type' => 'ENUM', 'constraint' => ['Type2', 'CCS', 'CHAdeMO', 'Schuko', 'Type1']],
            'power_kw'              => ['type' => 'DECIMAL', 'constraint' => '7,2', 'default' => 0],
            'status'                => ['type' => 'ENUM', 'constraint' => ['available', 'occupied', 'out_of_service', 'unknown'], 'default' => 'unknown'],
            'structured_tariff_json' => ['type' => 'JSON', 'null' => true],
            'last_status_update'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('charge_point_id', 'charge_points', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('charge_point_id');
        $this->forge->createTable('connectors');
    }

    public function down()
    {
        $this->forge->dropTable('connectors');
    }
}
