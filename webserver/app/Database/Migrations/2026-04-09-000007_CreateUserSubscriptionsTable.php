<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSubscriptionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'            => ['type' => 'INT', 'unsigned' => true],
            'plan_version_id'    => ['type' => 'INT', 'unsigned' => true],
            'status'             => ['type' => 'ENUM', 'constraint' => ['active', 'cancelled', 'expired', 'past_due'], 'default' => 'active'],
            'billing_cycle'      => ['type' => 'ENUM', 'constraint' => ['monthly', 'yearly']],
            'starts_at'          => ['type' => 'DATETIME'],
            'current_period_end' => ['type' => 'DATETIME'],
            'cancelled_at'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('plan_version_id', 'subscription_plan_versions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('user_subscriptions');
    }

    public function down()
    {
        $this->forge->dropTable('user_subscriptions');
    }
}
