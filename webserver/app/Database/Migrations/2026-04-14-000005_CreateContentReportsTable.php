<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentReportsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reporter_user_id' => ['type' => 'INT', 'unsigned' => true],
            'entity_type'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_id'        => ['type' => 'INT', 'unsigned' => true],
            'reason'           => ['type' => 'TEXT'],
            'status'           => ['type' => 'ENUM', 'constraint' => ['pending', 'reviewed', 'dismissed', 'actioned'], 'default' => 'pending'],
            'moderator_notes'  => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('reporter_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey('status');
        $this->forge->createTable('content_reports');
    }

    public function down()
    {
        $this->forge->dropTable('content_reports');
    }
}
