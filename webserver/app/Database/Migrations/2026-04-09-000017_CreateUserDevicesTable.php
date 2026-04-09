<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserDevicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'                => ['type' => 'INT', 'unsigned' => true],
            'platform'               => ['type' => 'ENUM', 'constraint' => ['android', 'ios', 'web']],
            'push_token'             => ['type' => 'VARCHAR', 'constraint' => 500],
            'device_name'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'app_version'            => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'notifications_enabled'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_active'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_seen_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey('push_token');
        $this->forge->addKey('user_id');
        $this->forge->createTable('user_devices');
    }

    public function down()
    {
        $this->forge->dropTable('user_devices');
    }
}
