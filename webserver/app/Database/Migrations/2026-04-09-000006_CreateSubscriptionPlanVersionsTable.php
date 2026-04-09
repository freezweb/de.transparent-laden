<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionPlanVersionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'plan_id'                         => ['type' => 'INT', 'unsigned' => true],
            'version'                         => ['type' => 'INT', 'unsigned' => true],
            'price_monthly_cent'              => ['type' => 'INT', 'unsigned' => true],
            'price_yearly_cent'               => ['type' => 'INT', 'unsigned' => true],
            'platform_fee_reduction_percent'  => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'features_json'                   => ['type' => 'JSON', 'null' => true],
            'valid_from'                      => ['type' => 'DATETIME'],
            'valid_until'                     => ['type' => 'DATETIME', 'null' => true],
            'created_at'                      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('plan_id');
        $this->forge->addForeignKey('plan_id', 'subscription_plans', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('subscription_plan_versions');
    }

    public function down()
    {
        $this->forge->dropTable('subscription_plan_versions');
    }
}
