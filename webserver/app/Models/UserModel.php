<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'email', 'password_hash', 'first_name', 'last_name',
        'phone', 'street', 'city', 'postal_code', 'country',
        'email_verified_at', 'status', 'stripe_customer_id',
    ];

    protected $validationRules = [
        'email'         => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password_hash' => 'required|min_length[60]',
        'first_name'    => 'permit_empty|max_length[100]',
        'last_name'     => 'permit_empty|max_length[100]',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function verifyEmail(int $userId): bool
    {
        return $this->update($userId, ['email_verified_at' => date('Y-m-d H:i:s')]);
    }
}
