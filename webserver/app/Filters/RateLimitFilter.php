<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $cache = service('cache');
        $ip = $request->getIPAddress();
        $maxRequests = (int) ($arguments[0] ?? 60);
        $window = (int) ($arguments[1] ?? 60);

        $key = 'rate_limit_' . md5($ip . '_' . current_url());
        $hits = (int) $cache->get($key);

        if ($hits >= $maxRequests) {
            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'error'   => 'Too Many Requests',
                    'message' => 'Rate limit exceeded. Try again later.',
                ])
                ->setHeader('Retry-After', (string) $window);
        }

        $cache->save($key, $hits + 1, $window);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
