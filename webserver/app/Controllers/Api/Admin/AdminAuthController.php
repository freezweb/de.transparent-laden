<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\ApiBaseController;
use App\Models\AdminUserModel;
use App\Models\AuditLogModel;
use App\Libraries\JwtManager;
use App\Libraries\TotpManager;

class AdminAuthController extends ApiBaseController
{
    private AdminUserModel $adminModel;
    private AuditLogModel  $auditModel;
    private JwtManager     $jwt;
    private TotpManager    $totp;

    public function __construct()
    {
        $this->adminModel = model(AdminUserModel::class);
        $this->auditModel = model(AuditLogModel::class);
        $this->jwt        = new JwtManager();
        $this->totp       = new TotpManager();
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
            'totp'     => 'required|exact_length[6]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data  = $this->request->getJSON(true);
        $admin = $this->adminModel->findByEmail($data['email']);

        if (! $admin || ! password_verify($data['password'], $admin['password_hash'])) {
            return $this->failUnauthorized('Invalid credentials');
        }

        if ($admin['status'] !== 'active') {
            return $this->failForbidden('Account not active. Status: ' . $admin['status']);
        }

        if (! $this->totp->verify($admin['totp_secret'], $data['totp'])) {
            return $this->failUnauthorized('Invalid TOTP code');
        }

        $token = $this->jwt->generateAccessToken([
            'sub'   => $admin['id'],
            'email' => $admin['email'],
            'role'  => 'admin',
            'admin_role' => $admin['role'],
        ]);

        $this->adminModel->update($admin['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        $this->auditModel->log('admin_users', $admin['id'], 'login', 'admin', $admin['id'], []);

        return $this->respond([
            'access_token' => $token,
            'admin'        => [
                'id'    => (int) $admin['id'],
                'email' => $admin['email'],
                'name'  => $admin['display_name'],
                'role'  => $admin['role'],
            ],
        ]);
    }

    public function invite()
    {
        $rules = [
            'email' => 'required|valid_email',
            'name'  => 'required|max_length[200]',
            'role'  => 'required|in_list[admin,viewer]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        $existing = $this->adminModel->findByEmail($data['email']);
        if ($existing) {
            return $this->fail('Admin with this email already exists', 409);
        }

        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $adminId = $this->adminModel->insert([
            'email'                  => $data['email'],
            'display_name'           => $data['name'],
            'role'                   => $data['role'],
            'status'                 => 'invited',
            'invitation_token_hash'  => hash('sha256', $token),
            'invitation_expires_at'  => $expiresAt,
        ]);

        $this->auditModel->log('admin_users', $adminId, 'invite', 'admin', $this->userId, [
            'email' => $data['email'],
            'role'  => $data['role'],
        ]);

        return $this->respondCreated([
            'admin_id'         => $adminId,
            'invitation_token' => $token,
            'expires_at'       => $expiresAt,
        ]);
    }

    public function setupTotp()
    {
        $rules = [
            'invitation_token' => 'required',
            'password'         => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data  = $this->request->getJSON(true);
        $admin = $this->adminModel->findByInvitationToken(hash('sha256', $data['invitation_token']));

        if (! $admin) {
            return $this->failNotFound('Invalid or expired invitation token');
        }

        if (strtotime($admin['invitation_expires_at']) < time()) {
            return $this->fail('Invitation has expired', 410);
        }

        $totpSecret    = $this->totp->generateSecret();
        $recoveryCodes = $this->totp->generateRecoveryCodes();

        $this->adminModel->update($admin['id'], [
            'password_hash'              => password_hash($data['password'], PASSWORD_ARGON2ID),
            'totp_secret_encrypted'      => $totpSecret,
            'recovery_codes_encrypted'   => json_encode($recoveryCodes),
            'status'                     => 'totp_pending',
            'invitation_token_hash'      => null,
            'invitation_expires_at'      => null,
        ]);

        $provisioningUri = $this->totp->getProvisioningUri($admin['email'], $totpSecret, 'EinfachLaden Admin');

        return $this->respond([
            'totp_secret'      => $totpSecret,
            'provisioning_uri' => $provisioningUri,
            'recovery_codes'   => $recoveryCodes,
        ]);
    }

    public function confirmTotp()
    {
        $rules = [
            'email' => 'required|valid_email',
            'totp'  => 'required|exact_length[6]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data  = $this->request->getJSON(true);
        $admin = $this->adminModel->findByEmail($data['email']);

        if (! $admin || $admin['status'] !== 'totp_pending') {
            return $this->failNotFound('Admin not in TOTP setup state');
        }

        if (! $this->totp->verify($admin['totp_secret_encrypted'], $data['totp'])) {
            return $this->failUnauthorized('Invalid TOTP code');
        }

        $this->adminModel->update($admin['id'], ['status' => 'active']);

        $this->auditModel->log('admin_users', $admin['id'], 'totp_confirmed', 'admin', $admin['id'], []);

        return $this->respond(['message' => 'TOTP confirmed. Account is now active.']);
    }
}
