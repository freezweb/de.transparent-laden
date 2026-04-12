<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRefreshTokenModel extends Model
{
    protected $table            = 'user_refresh_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $updatedField     = '';

    protected $allowedFields = [
        'user_id', 'token_hash', 'device_info', 'expires_at',
    ];

    public function findValidToken(string $tokenHash): ?array
    {
        return $this->where('token_hash', $tokenHash)
                     ->where('expires_at >', date('Y-m-d H:i:s'))
                     ->first();
    }

    public function revokeAllForUser(int $userId): bool
    {
        return $this->where('user_id', $userId)->delete();
    }

    public function cleanupExpired(): int
    {
        $builder = $this->builder();
        return $builder->where('expires_at <', date('Y-m-d H:i:s'))->delete()->resultID;
    }
}
