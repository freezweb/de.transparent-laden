<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('admin_users')->insert([
            'email'         => 'admin@einfach-laden.de',
            'display_name'  => 'Super Admin',
            'password_hash' => password_hash('ChangeMeImmediately!2024', PASSWORD_ARGON2ID),
            'role'          => 'super_admin',
            'status'        => 'totp_pending',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
