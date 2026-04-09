<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\ApiBaseController;
use App\Models\UserModel;
use App\Models\ChargingSessionModel;
use App\Models\InvoiceModel;
use App\Models\AuditLogModel;

class AdminDashboardController extends ApiBaseController
{
    public function stats()
    {
        $userModel    = model(UserModel::class);
        $sessionModel = model(ChargingSessionModel::class);
        $invoiceModel = model(InvoiceModel::class);

        $db = \Config\Database::connect();

        $totalUsers   = $userModel->where('deleted_at IS NULL')->countAllResults();
        $activeUsers  = $userModel->where('status', 'active')->where('deleted_at IS NULL')->countAllResults();

        $totalSessions = $sessionModel->countAllResults(false);
        $activeSessions = $sessionModel->where('status', 'active')->countAllResults();

        $totalRevenue = $db->table('invoices')
            ->selectSum('total_gross_cent')
            ->where('status', 'paid')
            ->get()->getRowArray();

        $todaySessions = $sessionModel
            ->where('created_at >=', date('Y-m-d 00:00:00'))
            ->countAllResults();

        return $this->respond([
            'stats' => [
                'users' => [
                    'total'  => $totalUsers,
                    'active' => $activeUsers,
                ],
                'sessions' => [
                    'total'  => $totalSessions,
                    'active' => $activeSessions,
                    'today'  => $todaySessions,
                ],
                'revenue' => [
                    'total_gross_cent' => (int) ($totalRevenue['total_gross_cent'] ?? 0),
                ],
            ],
        ]);
    }

    public function users()
    {
        $userModel = model(UserModel::class);
        $page      = (int) ($this->request->getGet('page') ?? 1);
        $perPage   = 20;
        $search    = $this->request->getGet('search');

        $builder = $userModel->where('deleted_at IS NULL');

        if ($search) {
            $builder->groupStart()
                ->like('email', $search)
                ->orLike('first_name', $search)
                ->orLike('last_name', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $users = $builder->orderBy('created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->findAll();

        $safeUsers = array_map(function ($u) {
            unset($u['password_hash']);
            return $u;
        }, $users);

        return $this->respond([
            'users' => $safeUsers,
            'total' => $total,
            'page'  => $page,
            'pages' => (int) ceil($total / $perPage),
        ]);
    }

    public function userDetail(int $id)
    {
        $userModel    = model(UserModel::class);
        $sessionModel = model(ChargingSessionModel::class);
        $invoiceModel = model(InvoiceModel::class);

        $user = $userModel->find($id);
        if (! $user) {
            return $this->failNotFound('User not found');
        }

        unset($user['password_hash']);

        $sessions = $sessionModel->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $invoices = $invoiceModel->getForUser($id, 1, 10);

        return $this->respond([
            'user'     => $user,
            'sessions' => $sessions,
            'invoices' => $invoices,
        ]);
    }

    public function blockUser(int $id)
    {
        $userModel = model(UserModel::class);
        $user      = $userModel->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        $userModel->update($id, ['status' => 'blocked']);

        $auditModel = model(AuditLogModel::class);
        $auditModel->log('users', $id, 'admin', $this->userId, 'block_user', []);

        return $this->respond(['message' => 'User blocked']);
    }

    public function unblockUser(int $id)
    {
        $userModel = model(UserModel::class);
        $user      = $userModel->find($id);

        if (! $user) {
            return $this->failNotFound('User not found');
        }

        $userModel->update($id, ['status' => 'active']);

        $auditModel = model(AuditLogModel::class);
        $auditModel->log('users', $id, 'admin', $this->userId, 'unblock_user', []);

        return $this->respond(['message' => 'User unblocked']);
    }

    public function sessions()
    {
        $sessionModel = model(ChargingSessionModel::class);
        $page         = (int) ($this->request->getGet('page') ?? 1);
        $perPage      = 20;
        $status       = $this->request->getGet('status');

        $builder = $sessionModel->builder();
        $builder->select('charging_sessions.*, users.email as user_email');
        $builder->join('users', 'users.id = charging_sessions.user_id', 'left');

        if ($status) {
            $builder->where('charging_sessions.status', $status);
        }

        $total = (clone $builder)->countAllResults(false);

        $sessions = $builder->orderBy('charging_sessions.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return $this->respond([
            'sessions' => $sessions,
            'total'    => $total,
            'page'     => $page,
            'pages'    => (int) ceil($total / $perPage),
        ]);
    }

    public function invoices()
    {
        $invoiceModel = model(InvoiceModel::class);
        $page         = (int) ($this->request->getGet('page') ?? 1);
        $perPage      = 20;

        $builder = $invoiceModel->builder();
        $builder->select('invoices.*, users.email as user_email');
        $builder->join('users', 'users.id = invoices.user_id', 'left');

        $total = (clone $builder)->countAllResults(false);

        $invoices = $builder->orderBy('invoices.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return $this->respond([
            'invoices' => $invoices,
            'total'    => $total,
            'page'     => $page,
            'pages'    => (int) ceil($total / $perPage),
        ]);
    }
}
