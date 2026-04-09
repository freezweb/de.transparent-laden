<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemConfigTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'config_key'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'config_value' => ['type' => 'TEXT'],
            'description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'updated_by'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('config_key');
        $this->forge->createTable('system_config');
    }

    public function down()
    {
        $this->forge->dropTable('system_config');
    }
}
