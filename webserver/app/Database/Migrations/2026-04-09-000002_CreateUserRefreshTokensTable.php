<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRefreshTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true],
            'token_hash'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'device_info' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'expires_at'  => ['type' => 'DATETIME'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('token_hash');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_refresh_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('user_refresh_tokens');
    }
}
