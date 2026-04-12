<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeUserNamesNullable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'last_name'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('users', [
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'last_name'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
        ]);
    }
}
