<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQrScanLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'qr_content'      => ['type' => 'TEXT'],
            'latitude'        => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'longitude'       => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'recognized'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'charge_point_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'provider_name'   => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'is_startable'    => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'ip_address'      => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('recognized');
        $this->forge->createTable('qr_scan_logs');
    }

    public function down()
    {
        $this->forge->dropTable('qr_scan_logs');
    }
}
