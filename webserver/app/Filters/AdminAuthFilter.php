<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtManager;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || ! str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized']);
        }

        $token = substr($authHeader, 7);
        $jwtManager = new JwtManager();
        $decoded = $jwtManager->validateAccessToken($token);

        if (! $decoded || ($decoded->role ?? '') === 'user') {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Forbidden', 'message' => 'Admin access required']);
        }

        $request->adminId   = $decoded->sub;
        $request->adminRole = $decoded->role;

        // Check specific role if arguments provided
        if ($arguments) {
            $requiredRoles = $arguments;
            if (! in_array($decoded->role, $requiredRoles)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'Forbidden', 'message' => 'Insufficient role']);
            }
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
