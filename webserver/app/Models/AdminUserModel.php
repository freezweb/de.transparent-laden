<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminUserModel extends Model
{
    protected $table            = 'admin_users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'email', 'password_hash', 'display_name', 'role', 'status',
        'totp_secret_encrypted', 'totp_verified_at', 'recovery_codes_encrypted',
        'invited_by', 'invitation_token_hash', 'invitation_expires_at',
        'last_login_at',
    ];

    protected $validationRules = [
        'email'        => 'required|valid_email|is_unique[admin_users.email,id,{id}]',
        'display_name' => 'required|max_length[100]',
        'role'         => 'required|in_list[super_admin,admin,viewer]',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function findByInvitationToken(string $token): ?array
    {
        return $this->where('invitation_token_hash', $token)
                     ->where('invitation_expires_at >', date('Y-m-d H:i:s'))
                     ->first();
    }
}
