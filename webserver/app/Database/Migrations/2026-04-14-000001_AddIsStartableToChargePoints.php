<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsStartableToChargePoints extends Migration
{
    public function up()
    {
        $this->forge->addColumn('charge_points', [
            'is_startable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => null,
                'after'      => 'is_active',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('charge_points', 'is_startable');
    }
}
