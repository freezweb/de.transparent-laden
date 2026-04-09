<?php

namespace App\Controllers\Api;

use App\Models\SubscriptionPlanModel;
use App\Models\SubscriptionPlanVersionModel;
use App\Models\UserSubscriptionModel;
use App\Models\AuditLogModel;

class SubscriptionController extends ApiBaseController
{
    private SubscriptionPlanModel $planModel;
    private SubscriptionPlanVersionModel $planVersionModel;
    private UserSubscriptionModel $subscriptionModel;

    public function __construct()
    {
        $this->planModel        = model(SubscriptionPlanModel::class);
        $this->planVersionModel = model(SubscriptionPlanVersionModel::class);
        $this->subscriptionModel = model(UserSubscriptionModel::class);
    }

    public function plans()
    {
        $plans = $this->planModel->getActivePlans();

        foreach ($plans as &$plan) {
            $version = $this->planVersionModel->getCurrentVersion($plan['id']);
            $plan['current_version'] = $version;
            $plan['features'] = json_decode($plan['features_json'], true);
            unset($plan['features_json']);
        }

        return $this->respond(['plans' => $plans]);
    }

    public function current()
    {
        $sub = $this->subscriptionModel->getActiveForUser($this->userId);

        if (! $sub) {
            return $this->respond(['subscription' => null]);
        }

        $version = $this->planVersionModel->find($sub['plan_version_id']);
        $plan = $version ? $this->planModel->find($version['plan_id']) : null;
        $sub['plan'] = $plan;

        return $this->respond(['subscription' => $sub]);
    }

    public function subscribe()
    {
        $data = $this->request->getJSON(true);
        $planSlug = $data['plan_slug'] ?? '';
        $billingCycle = $data['billing_cycle'] ?? 'monthly';

        if (! in_array($billingCycle, ['monthly', 'yearly'])) {
            return $this->failValidationErrors(['billing_cycle' => 'Must be monthly or yearly']);
        }

        $plan = $this->planModel->findBySlug($planSlug);
        if (! $plan || ! $plan['is_active']) {
            return $this->failNotFound('Plan not found');
        }

        $version = $this->planVersionModel->getCurrentVersion($plan['id']);
        if (! $version) {
            return $this->fail('No active version for this plan');
        }

        // Cancel existing subscription
        $existing = $this->subscriptionModel->getActiveForUser($this->userId);
        if ($existing) {
            $this->subscriptionModel->update($existing['id'], [
                'status'       => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $periodEnd = $billingCycle === 'monthly'
            ? date('Y-m-d H:i:s', strtotime('+1 month'))
            : date('Y-m-d H:i:s', strtotime('+1 year'));

        $subId = $this->subscriptionModel->insert([
            'user_id'            => $this->userId,
            'plan_version_id'    => $version['id'],
            'status'             => 'active',
            'billing_cycle'      => $billingCycle,
            'starts_at'          => date('Y-m-d H:i:s'),
            'current_period_end' => $periodEnd,
        ]);

        model(AuditLogModel::class)->log('subscription', $subId, 'subscribe', 'user', $this->userId);

        return $this->respondCreated(['subscription_id' => $subId, 'message' => 'Subscription created']);
    }

    public function cancel()
    {
        $sub = $this->subscriptionModel->getActiveForUser($this->userId);
        if (! $sub) {
            return $this->failNotFound('No active subscription');
        }

        $this->subscriptionModel->update($sub['id'], [
            'status'       => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);

        model(AuditLogModel::class)->log('subscription', $sub['id'], 'cancel', 'user', $this->userId);

        return $this->respond(['message' => 'Subscription cancelled']);
    }
}
