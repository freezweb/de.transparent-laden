<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'password_hash'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'first_name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'last_name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'             => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'street'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'city'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'postal_code'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'country'           => ['type' => 'VARCHAR', 'constraint' => 2, 'default' => 'DE'],
            'email_verified_at' => ['type' => 'DATETIME', 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'blocked', 'pending'], 'default' => 'pending'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
