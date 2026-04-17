<?php

namespace App\Controllers\Api;

use App\Libraries\ChargingService;
use App\Libraries\LiveCostService;
use App\Libraries\EmailService;
use App\Models\ChargingSessionModel;
use App\Models\UserModel;

class ChargingController extends ApiBaseController
{
    private ChargingService $chargingService;
    private LiveCostService $liveCostService;
    private ChargingSessionModel $sessionModel;

    public function __construct()
    {
        $this->chargingService = new ChargingService();
        $this->liveCostService = new LiveCostService();
        $this->sessionModel    = model(ChargingSessionModel::class);
    }

    public function start()
    {
        $data = $this->request->getJSON(true);

        $rules = [
            'connector_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Check contract acceptance + withdrawal waiver
        $userModel = model(UserModel::class);
        $user = $userModel->find($this->userId);
        if (empty($user['terms_accepted_at'])) {
            return $this->fail('Bitte zuerst die AGB akzeptieren (Profil → Vertrag).', 422);
        }
        if (empty($user['withdrawal_waived_at'])) {
            return $this->fail('Bitte zuerst den Widerrufsverzicht erklären (Profil → Vertrag).', 422);
        }

        try {
            $session = $this->chargingService->startSession(
                $this->userId,
                (int) $data['connector_id'],
                isset($data['payment_method_id']) ? (int) $data['payment_method_id'] : null
            );

            return $this->respondCreated(['session' => $session]);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function stop(int $sessionId)
    {
        try {
            $session = $this->chargingService->stopSession($this->userId, $sessionId);

            // Send charging receipt email
            try {
                $userModel = model(UserModel::class);
                $user = $userModel->find($this->userId);
                if ($user) {
                    $mailer = new EmailService();
                    $mailer->sendChargingReceipt(
                        $user['email'],
                        $user['first_name'] ?? '',
                        $session,
                        $this->userId
                    );
                }
            } catch (\Throwable $e) {
                log_message('error', 'Charging receipt email failed: ' . $e->getMessage());
            }

            return $this->respond(['session' => $session]);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function status(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (! $session || $session['user_id'] !== $this->userId) {
            return $this->failNotFound('Session not found');
        }

        return $this->respond(['session' => $session]);
    }

    public function liveStatus(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (! $session || $session['user_id'] !== $this->userId) {
            return $this->failNotFound('Session not found');
        }

        $liveData = $this->liveCostService->getLiveData($sessionId);
        if (! $liveData) {
            return $this->fail('No live data available');
        }

        return $this->respond($liveData);
    }

    public function active()
    {
        $session = $this->sessionModel->getActiveForUser($this->userId);
        return $this->respond(['session' => $session]);
    }

    public function history()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = min(50, (int) ($this->request->getGet('per_page') ?? 20));

        $sessions = $this->sessionModel->getHistoryForUser($this->userId, $page, $perPage);

        return $this->respond([
            'sessions' => $sessions,
            'pager'    => $this->sessionModel->pager->getDetails(),
        ]);
    }
}
