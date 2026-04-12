<?php

namespace App\Controllers\Api;

use App\Libraries\AuthService;

class AuthController extends ApiBaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register()
    {
        $rules = [
            'email'      => 'required|valid_email',
            'password'   => 'required|min_length[8]',
            'first_name' => 'permit_empty|max_length[100]',
            'last_name'  => 'permit_empty|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        try {
            $tokens = $this->authService->register($data);
            return $this->respondCreated($tokens);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return $this->failResourceExists('Email already registered');
            }
            log_message('error', 'Registration failed: ' . $e->getMessage());
            return $this->failServerError('Registration failed');
        } catch (\Exception $e) {
            log_message('error', 'Registration failed: ' . $e->getMessage());
            return $this->failServerError('Registration failed');
        }
    }

    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $deviceInfo = $this->request->getHeaderLine('X-Device-Info') ?: null;

        $tokens = $this->authService->login($data['email'], $data['password'], $deviceInfo);

        if (! $tokens) {
            return $this->failUnauthorized('Invalid credentials');
        }

        return $this->respond($tokens);
    }

    public function refresh()
    {
        $data = $this->request->getJSON(true);
        $refreshToken = $data['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            return $this->failValidationErrors(['refresh_token' => 'Required']);
        }

        $deviceInfo = $this->request->getHeaderLine('X-Device-Info') ?: null;
        $tokens = $this->authService->refresh($refreshToken, $deviceInfo);

        if (! $tokens) {
            return $this->failUnauthorized('Invalid refresh token');
        }

        return $this->respond($tokens);
    }

    public function logout()
    {
        $data = $this->request->getJSON(true);
        $this->authService->logout($this->userId, $data['refresh_token'] ?? null);

        return $this->respondNoContent();
    }
}
