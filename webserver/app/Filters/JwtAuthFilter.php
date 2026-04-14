<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtManager;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || ! str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized', 'message' => 'Missing or invalid Authorization header']);
        }

        $token = substr($authHeader, 7);
        $jwtManager = new JwtManager();
        $decoded = $jwtManager->validateAccessToken($token);

        if (! $decoded) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized', 'message' => 'Invalid or expired token']);
        }

        // Store user data in request for controllers
        $request->userId    = $decoded->sub ?? 0;
        $request->userEmail = isset($decoded->email) ? $decoded->email : '';
        $request->userRole  = isset($decoded->role) ? $decoded->role : 'user';

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed
    }
}
