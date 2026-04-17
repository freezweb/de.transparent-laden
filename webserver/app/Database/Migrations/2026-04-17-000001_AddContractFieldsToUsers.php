<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContractFieldsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'terms_accepted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'email_verified_at',
            ],
            'terms_version' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'terms_accepted_at',
            ],
            'withdrawal_waived_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'terms_version',
            ],
            'withdrawal_waiver_ip' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'after' => 'withdrawal_waived_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'terms_accepted_at',
            'terms_version',
            'withdrawal_waived_at',
            'withdrawal_waiver_ip',
        ]);
    }
}
