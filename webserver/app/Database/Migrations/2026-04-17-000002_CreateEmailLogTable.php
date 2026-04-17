<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'to_email'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'template'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'subject'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'status'       => ['type' => 'ENUM', 'constraint' => ['sent', 'failed'], 'default' => 'sent'],
            'error_message'=> ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('template');
        $this->forge->createTable('email_log');
    }

    public function down()
    {
        $this->forge->dropTable('email_log');
    }
}
