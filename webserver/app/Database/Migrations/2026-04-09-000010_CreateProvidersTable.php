<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProvidersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 150],
            'slug'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'adapter_class'     => ['type' => 'VARCHAR', 'constraint' => 200],
            'config_encrypted'  => ['type' => 'TEXT', 'null' => true],
            'roaming_fee_type'  => ['type' => 'ENUM', 'constraint' => ['none', 'fixed', 'percentage'], 'default' => 'none'],
            'roaming_fee_value' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'is_active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('providers');
    }

    public function down()
    {
        $this->forge->dropTable('providers');
    }
}
