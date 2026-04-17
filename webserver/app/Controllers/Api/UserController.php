<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Libraries\EmailService;

class UserController extends ApiBaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
    }

    public function profile()
    {
        $user = $this->userModel->find($this->userId);
        if (! $user) {
            return $this->failNotFound('User not found');
        }

        unset($user['password_hash'], $user['deleted_at']);
        return $this->respond($user);
    }

    public function update()
    {
        $rules = [
            'first_name'  => 'permit_empty|max_length[100]',
            'last_name'   => 'permit_empty|max_length[100]',
            'phone'       => 'permit_empty|max_length[20]',
            'street'      => 'permit_empty|max_length[200]',
            'city'        => 'permit_empty|max_length[100]',
            'postal_code' => 'permit_empty|max_length[10]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $allowed = ['first_name', 'last_name', 'phone', 'street', 'city', 'postal_code', 'country'];
        $updateData = array_intersect_key($data, array_flip($allowed));

        if (empty($updateData)) {
            return $this->failValidationErrors(['message' => 'No valid fields provided']);
        }

        $this->userModel->update($this->userId, $updateData);

        return $this->respond(['message' => 'Profile updated']);
    }

    public function changePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $user = $this->userModel->find($this->userId);

        if (! password_verify($data['current_password'], $user['password_hash'])) {
            return $this->failUnauthorized('Current password is incorrect');
        }

        $this->userModel->update($this->userId, [
            'password_hash' => password_hash($data['new_password'], PASSWORD_ARGON2ID),
        ]);

        // Notify user about password change
        try {
            $mailer = new EmailService();
            $mailer->sendPasswordChanged($user['email'], $user['first_name'] ?? '', $this->userId);
        } catch (\Throwable $e) {
            log_message('error', 'Password change email failed: ' . $e->getMessage());
        }

        return $this->respond(['message' => 'Password changed']);
    }

    public function delete()
    {
        $this->userModel->delete($this->userId);
        return $this->respondDeleted(['message' => 'Account deleted']);
    }
}
