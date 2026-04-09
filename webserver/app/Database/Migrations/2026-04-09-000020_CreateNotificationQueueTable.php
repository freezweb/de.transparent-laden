<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationQueueTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'                => ['type' => 'INT', 'unsigned' => true],
            'session_id'             => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'event_type'             => ['type' => 'VARCHAR', 'constraint' => 50],
            'payload_json'           => ['type' => 'JSON', 'null' => true],
            'target_device_ids_json' => ['type' => 'JSON', 'null' => true],
            'attempt_count'          => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'max_attempts'           => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 3],
            'next_attempt_at'        => ['type' => 'DATETIME', 'null' => true],
            'last_error'             => ['type' => 'TEXT', 'null' => true],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['pending', 'processing', 'sent', 'failed', 'skipped'], 'default' => 'pending'],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['status', 'next_attempt_at']);
        $this->forge->createTable('notification_queue');
    }

    public function down()
    {
        $this->forge->dropTable('notification_queue');
    }
}
