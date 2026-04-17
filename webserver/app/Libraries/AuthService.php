<?php

namespace App\Libraries;

use App\Models\UserModel;
use App\Models\UserRefreshTokenModel;
use App\Models\AuditLogModel;

class AuthService
{
    private UserModel $userModel;
    private UserRefreshTokenModel $refreshTokenModel;
    private JwtManager $jwtManager;
    private AuditLogModel $auditLog;

    public function __construct()
    {
        $this->userModel         = model(UserModel::class);
        $this->refreshTokenModel = model(UserRefreshTokenModel::class);
        $this->jwtManager        = new JwtManager();
        $this->auditLog          = model(AuditLogModel::class);
    }

    public function register(array $data): array
    {
        $userId = $this->userModel->insert([
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'first_name'    => $data['first_name'] ?? null,
            'last_name'     => $data['last_name'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'status'        => 'pending',
        ]);

        $this->auditLog->log('user', $userId, 'register', 'user', $userId);

        // Send welcome email
        try {
            $mailer = new EmailService();
            $mailer->sendWelcome($data['email'], $data['first_name'] ?? '', (int) $userId);
        } catch (\Throwable $e) {
            log_message('error', 'Welcome email failed: ' . $e->getMessage());
        }

        return $this->issueTokens($userId, $data['email']);
    }

    public function login(string $email, string $password, ?string $deviceInfo = null): ?array
    {
        $user = $this->userModel->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return null;
        }

        if ($user['status'] === 'blocked') {
            return null;
        }

        $this->auditLog->log('user', $user['id'], 'login', 'user', $user['id']);

        return $this->issueTokens($user['id'], $user['email'], $deviceInfo);
    }

    public function refresh(string $refreshToken, ?string $deviceInfo = null): ?array
    {
        $hash = $this->jwtManager->hashRefreshToken($refreshToken);
        $stored = $this->refreshTokenModel->findValidToken($hash);

        if (! $stored) {
            return null;
        }

        // Rotate: delete old token
        $this->refreshTokenModel->delete($stored['id']);

        $user = $this->userModel->find($stored['user_id']);
        if (! $user || $user['status'] === 'blocked') {
            return null;
        }

        return $this->issueTokens($user['id'], $user['email'], $deviceInfo);
    }

    public function logout(int $userId, ?string $refreshToken = null): void
    {
        if ($refreshToken) {
            $hash = $this->jwtManager->hashRefreshToken($refreshToken);
            $this->refreshTokenModel->where('token_hash', $hash)->delete();
        } else {
            $this->refreshTokenModel->revokeAllForUser($userId);
        }

        $this->auditLog->log('user', $userId, 'logout', 'user', $userId);
    }

    private function issueTokens(int $userId, string $email, ?string $deviceInfo = null): array
    {
        $accessToken  = $this->jwtManager->generateAccessToken($userId, $email);
        $refreshToken = $this->jwtManager->generateRefreshToken();

        $this->refreshTokenModel->insert([
            'user_id'     => $userId,
            'token_hash'  => $this->jwtManager->hashRefreshToken($refreshToken),
            'device_info' => $deviceInfo,
            'expires_at'  => date('Y-m-d H:i:s', time() + $this->jwtManager->getRefreshTtl()),
        ]);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int) env('jwt.accessTtl', 900),
        ];
    }
}
