<?php

namespace App\Libraries;

use App\Libraries\Entities\PricingResult;
use App\Libraries\Entities\ResolvedTariff;
use App\Libraries\Entities\StructuredTariff;
use App\Models\PaymentFeeModelModel;
use App\Models\ProviderModel;
use App\Models\SystemConfigModel;
use App\Models\UserSubscriptionModel;
use App\Models\SubscriptionPlanVersionModel;
use App\Models\PricingSnapshotModel;

class PricingEngine
{
    private TariffResolver $tariffResolver;
    private PaymentFeeModelModel $feeModelModel;
    private SystemConfigModel $systemConfig;
    private UserSubscriptionModel $subscriptionModel;
    private SubscriptionPlanVersionModel $planVersionModel;
    private PricingSnapshotModel $snapshotModel;

    public function __construct()
    {
        $this->tariffResolver    = new TariffResolver();
        $this->feeModelModel     = model(PaymentFeeModelModel::class);
        $this->systemConfig      = model(SystemConfigModel::class);
        $this->subscriptionModel = model(UserSubscriptionModel::class);
        $this->planVersionModel  = model(SubscriptionPlanVersionModel::class);
        $this->snapshotModel     = model(PricingSnapshotModel::class);
    }

    public function calculatePrice(
        ?StructuredTariff $tariff,
        array $provider,
        int $userId,
        string $paymentType,
        int $connectorId
    ): PricingResult {
        $resolvedTariff = $this->tariffResolver->resolve($tariff);

        // Roaming fee
        $roamingFeeCent = $this->calculateRoamingFee($provider, $resolvedTariff->energyPricePerKwhCent);

        // Platform fee
        $basePlatformFeeCent = (int) ($this->systemConfig->getValue('platform_fee_cent_per_kwh', '5'));
        $reductionPercent = $this->getSubscriptionReduction($userId);
        $effectivePlatformFee = (int) round($basePlatformFeeCent * (1 - $reductionPercent / 100));

        // Payment fee
        $estimatedChargeCent = $resolvedTariff->energyPricePerKwhCent * 30; // estimate 30 kWh
        $feeModel = $this->feeModelModel->getActiveForType($paymentType);
        $paymentFeeCent = 0;
        $paymentFeeModelId = null;
        if ($feeModel) {
            $paymentFeeCent = $this->feeModelModel->calculateFee($feeModel, $estimatedChargeCent);
            $paymentFeeModelId = $feeModel['id'];
        }

        // Estimated total per kWh
        $estimatedTotal = $resolvedTariff->energyPricePerKwhCent + $roamingFeeCent + $effectivePlatformFee;

        // Transparency breakdown
        $transparency = [
            'components' => [
                ['label' => 'Energiepreis', 'value_cent' => $resolvedTariff->energyPricePerKwhCent, 'unit' => 'ct/kWh'],
                ['label' => 'Zeitpreis', 'value_cent' => $resolvedTariff->timePricePerMinCent, 'unit' => 'ct/min'],
                ['label' => 'Blockiergebühr', 'value_cent' => $resolvedTariff->blockingFeePerMinCent, 'unit' => 'ct/min', 'free_minutes' => $resolvedTariff->blockingFreeMinutes],
                ['label' => 'Startgebühr', 'value_cent' => $resolvedTariff->startFeeCent, 'unit' => 'einmalig'],
                ['label' => 'Roaming', 'value_cent' => $roamingFeeCent, 'unit' => 'ct/kWh'],
                ['label' => 'Plattformgebühr', 'value_cent' => $effectivePlatformFee, 'unit' => 'ct/kWh', 'reduction' => $reductionPercent . '%'],
                ['label' => 'Zahlungsgebühr', 'value_cent' => $paymentFeeCent, 'unit' => 'geschätzt'],
            ],
            'estimated_total_per_kwh_cent' => $estimatedTotal,
        ];

        $result = new PricingResult(
            $resolvedTariff,
            $roamingFeeCent,
            $basePlatformFeeCent,
            $reductionPercent,
            $effectivePlatformFee,
            $paymentFeeCent,
            $paymentFeeModelId,
            $estimatedTotal,
            $transparency
        );

        // Persist snapshot
        $snapshotData = array_merge($result->toSnapshotData(), [
            'connector_id'          => $connectorId,
            'provider_id'           => $provider['id'],
            'snapshot_time'         => date('Y-m-d H:i:s'),
            'structured_tariff_json' => $tariff ? json_encode($tariff->toArray()) : null,
        ]);
        $this->snapshotModel->insert($snapshotData);

        return $result;
    }

    public function calculateSessionCost(array $session, array $snapshot): array
    {
        $energyCost    = (int) round($session['energy_kwh'] * $snapshot['energy_price_per_kwh_cent']);
        $timeCost      = (int) round(($session['duration_seconds'] / 60) * $snapshot['time_price_per_min_cent']);
        $blockingSeconds = max(0, $session['blocking_duration_seconds'] - $snapshot['blocking_free_minutes'] * 60);
        $blockingCost  = (int) round(($blockingSeconds / 60) * $snapshot['blocking_fee_per_min_cent']);
        $startFee      = (int) $snapshot['start_fee_cent'];
        $roamingFee    = (int) round($session['energy_kwh'] * $snapshot['roaming_fee_cent']);
        $platformFee   = (int) round($session['energy_kwh'] * $snapshot['platform_fee_effective_cent']);

        $subtotal      = $energyCost + $timeCost + $blockingCost + $startFee + $roamingFee + $platformFee;

        // Payment fee on actual amount
        $paymentFee = 0;
        if ($snapshot['payment_fee_model_id']) {
            $feeModel = $this->feeModelModel->find($snapshot['payment_fee_model_id']);
            if ($feeModel) {
                $paymentFee = $this->feeModelModel->calculateFee($feeModel, $subtotal);
            }
        }

        $total = $subtotal + $paymentFee;
        $minBilling = (int) $snapshot['min_billing_amount_cent'];
        if ($minBilling > 0 && $total < $minBilling) {
            $total = $minBilling;
        }

        return [
            'energy_cost_cent'   => $energyCost,
            'time_cost_cent'     => $timeCost,
            'blocking_cost_cent' => $blockingCost,
            'start_fee_cent'     => $startFee,
            'roaming_fee_cent'   => $roamingFee,
            'platform_fee_cent'  => $platformFee,
            'payment_fee_cent'   => $paymentFee,
            'total_price_cent'   => $total,
        ];
    }

    private function calculateRoamingFee(array $provider, int $energyPriceCent): int
    {
        return match ($provider['roaming_fee_type']) {
            'fixed'      => (int) round($provider['roaming_fee_value'] * 100),
            'percentage' => (int) round($energyPriceCent * ($provider['roaming_fee_value'] / 100)),
            default      => 0,
        };
    }

    private function getSubscriptionReduction(int $userId): float
    {
        $sub = $this->subscriptionModel->getActiveForUser($userId);
        if (! $sub) {
            return 0.0;
        }

        $version = $this->planVersionModel->find($sub['plan_version_id']);
        return $version ? (float) $version['platform_fee_reduction_percent'] : 0.0;
    }
}
