<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'entity_type'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_id'    => ['type' => 'INT', 'unsigned' => true],
            'action'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'actor_type'   => ['type' => 'ENUM', 'constraint' => ['user', 'admin', 'system'], 'default' => 'system'],
            'actor_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'changes_json' => ['type' => 'JSON', 'null' => true],
            'ip_address'   => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('audit_log');
    }

    public function down()
    {
        $this->forge->dropTable('audit_log');
    }
}
