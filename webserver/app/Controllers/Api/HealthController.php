<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;

class HealthController extends ApiBaseController
{
    public function index(): ResponseInterface
    {
        $db = \Config\Database::connect();
        $dbOk = false;

        try {
            $db->query('SELECT 1');
            $dbOk = true;
        } catch (\Throwable $e) {
            // DB nicht erreichbar
        }

        $status = $dbOk ? 'ok' : 'degraded';
        $httpCode = $dbOk ? 200 : 503;

        return $this->respond([
            'status'    => $status,
            'timestamp' => date('c'),
            'version'   => '1.0.0',
            'checks'    => [
                'database' => $dbOk ? 'connected' : 'unreachable',
            ],
        ], $httpCode);
    }
}
