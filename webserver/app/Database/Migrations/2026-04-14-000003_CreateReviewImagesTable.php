<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReviewImagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'review_id'      => ['type' => 'INT', 'unsigned' => true],
            'file_path'      => ['type' => 'VARCHAR', 'constraint' => 500],
            'thumbnail_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('review_id', 'reviews', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('review_id');
        $this->forge->createTable('review_images');
    }

    public function down()
    {
        $this->forge->dropTable('review_images');
    }
}
