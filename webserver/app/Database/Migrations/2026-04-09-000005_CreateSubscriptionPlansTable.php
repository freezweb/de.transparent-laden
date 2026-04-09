<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionPlansTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'                            => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'                            => ['type' => 'VARCHAR', 'constraint' => 50],
            'description'                     => ['type' => 'TEXT', 'null' => true],
            'price_monthly_cent'              => ['type' => 'INT', 'unsigned' => true],
            'price_yearly_cent'               => ['type' => 'INT', 'unsigned' => true],
            'platform_fee_reduction_percent'  => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
            'features_json'                   => ['type' => 'JSON', 'null' => true],
            'is_active'                       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order'                      => ['type' => 'INT', 'default' => 0],
            'created_at'                      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('subscription_plans');
    }

    public function down()
    {
        $this->forge->dropTable('subscription_plans');
    }
}
