<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtManager
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $accessTtl;
    private int $refreshTtl;
    private string $issuer;

    public function __construct()
    {
        $this->secretKey  = env('jwt.secret');
        $this->accessTtl  = (int) env('jwt.accessTtl', 900);
        $this->refreshTtl = (int) env('jwt.refreshTtl', 604800);
        $this->issuer     = env('jwt.issuer', 'einfach-laden');
    }

    public function generateAccessToken(int $userId, string $email, string $role = 'user'): string
    {
        $now = time();
        $payload = [
            'iss'  => $this->issuer,
            'sub'  => $userId,
            'email' => $email,
            'role' => $role,
            'iat'  => $now,
            'exp'  => $now + $this->accessTtl,
            'type' => 'access',
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getRefreshTtl(): int
    {
        return $this->refreshTtl;
    }

    public function validateAccessToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            if ($decoded->type !== 'access') {
                return null;
            }
            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function hashRefreshToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
