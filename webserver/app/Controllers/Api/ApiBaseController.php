<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class ApiBaseController extends BaseController
{
    use ResponseTrait;

    protected int $userId = 0;
    protected string $userEmail = '';

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userId    = $request->userId ?? 0;
        $this->userEmail = $request->userEmail ?? '';
    }
}
