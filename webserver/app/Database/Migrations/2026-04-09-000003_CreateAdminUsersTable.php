<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'                      => ['type' => 'VARCHAR', 'constraint' => 255],
            'password_hash'              => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'display_name'               => ['type' => 'VARCHAR', 'constraint' => 100],
            'role'                       => ['type' => 'ENUM', 'constraint' => ['super_admin', 'admin', 'viewer'], 'default' => 'admin'],
            'status'                     => ['type' => 'ENUM', 'constraint' => ['invited', 'totp_pending', 'active', 'blocked'], 'default' => 'invited'],
            'totp_secret_encrypted'      => ['type' => 'TEXT', 'null' => true],
            'totp_verified_at'           => ['type' => 'DATETIME', 'null' => true],
            'recovery_codes_encrypted'   => ['type' => 'TEXT', 'null' => true],
            'invited_by'                 => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'invitation_token_hash'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'invitation_expires_at'      => ['type' => 'DATETIME', 'null' => true],
            'last_login_at'              => ['type' => 'DATETIME', 'null' => true],
            'created_at'                 => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                 => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('admin_users');
    }

    public function down()
    {
        $this->forge->dropTable('admin_users');
    }
}
