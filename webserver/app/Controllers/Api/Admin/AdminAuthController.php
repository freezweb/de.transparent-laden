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
            'totp'     => 'permit_empty|exact_length[6]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data  = $this->request->getJSON(true);
        $admin = $this->adminModel->findByEmail($data['email']);

        if (! $admin || ! password_verify($data['password'], $admin['password_hash'])) {
            return $this->failUnauthorized('Invalid credentials');
        }

        if ($admin['status'] === 'blocked') {
            return $this->failForbidden('Account is blocked');
        }

        // Admin has no TOTP set up yet → redirect to setup
        if (empty($admin['totp_secret_encrypted'])) {
            return $this->respond([
                'requires_totp_setup' => true,
                'message'             => 'TOTP setup required before first login.',
            ]);
        }

        // Admin has TOTP but status is still totp_pending → redirect to confirm
        if ($admin['status'] === 'totp_pending') {
            return $this->respond([
                'requires_totp_setup' => true,
                'message'             => 'TOTP confirmation pending.',
            ]);
        }

        // TOTP code required for active accounts
        if (empty($data['totp'])) {
            return $this->failValidationErrors(['totp' => 'TOTP code is required']);
        }

        if (! $this->totp->verify($admin['totp_secret_encrypted'], $data['totp'])) {
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
            'user'         => [
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
        $data = $this->request->getJSON(true);
        $admin = null;

        // Two paths: (A) via email+password or (B) via invitation_token+password
        if (! empty($data['email']) && ! empty($data['password'])) {
            // Path A: Existing admin without TOTP (first login flow)
            $admin = $this->adminModel->findByEmail($data['email']);
            if (! $admin || ! password_verify($data['password'], $admin['password_hash'])) {
                return $this->failUnauthorized('Invalid credentials');
            }
        } elseif (! empty($data['invitation_token']) && ! empty($data['password'])) {
            // Path B: Invited admin via token
            $admin = $this->adminModel->findByInvitationToken(hash('sha256', $data['invitation_token']));
            if (! $admin) {
                return $this->failNotFound('Invalid or expired invitation token');
            }
            if (strtotime($admin['invitation_expires_at']) < time()) {
                return $this->fail('Invitation has expired', 410);
            }
        } else {
            return $this->failValidationErrors(['error' => 'Provide email+password or invitation_token+password']);
        }

        $totpSecret    = $this->totp->generateSecret();
        $recoveryCodes = $this->totp->generateRecoveryCodes();

        $updateData = [
            'totp_secret_encrypted'      => $totpSecret,
            'recovery_codes_encrypted'   => json_encode($recoveryCodes),
            'status'                     => 'totp_pending',
            'invitation_token_hash'      => null,
            'invitation_expires_at'      => null,
        ];
        // Only set password if coming from invitation (Path B)
        if (! empty($data['invitation_token'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        }

        $this->adminModel->update($admin['id'], $updateData);

        $provisioningUri = $this->totp->getProvisioningUri($totpSecret, $admin['email'], 'TransparentLaden Admin');

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

        $this->adminModel->update($admin['id'], [
            'status'           => 'active',
            'totp_verified_at' => date('Y-m-d H:i:s'),
        ]);

        $token = $this->jwt->generateAccessToken([
            'sub'   => $admin['id'],
            'email' => $admin['email'],
            'role'  => 'admin',
            'admin_role' => $admin['role'],
        ]);

        $this->auditModel->log('admin_users', $admin['id'], 'totp_confirmed', 'admin', $admin['id'], []);

        return $this->respond([
            'message'      => 'TOTP confirmed. Account is now active.',
            'access_token' => $token,
            'user'         => [
                'id'    => (int) $admin['id'],
                'email' => $admin['email'],
                'name'  => $admin['display_name'],
                'role'  => $admin['role'],
            ],
        ]);
    }
}
