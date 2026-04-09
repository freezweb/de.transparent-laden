<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChargePointsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'provider_id'   => ['type' => 'INT', 'unsigned' => true],
            'external_id'   => ['type' => 'VARCHAR', 'constraint' => 200],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'address'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'city'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'postal_code'   => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'country'       => ['type' => 'VARCHAR', 'constraint' => 2, 'default' => 'DE'],
            'latitude'      => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'longitude'     => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'operator_name' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_seen_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('provider_id', 'providers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['provider_id', 'external_id']);
        $this->forge->createTable('charge_points');
    }

    public function down()
    {
        $this->forge->dropTable('charge_points');
    }
}
