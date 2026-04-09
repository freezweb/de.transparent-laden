<?php

namespace App\Libraries\Entities;

/**
 * Complete pricing result with all cost components (immutable).
 */
class PricingResult
{
    public readonly ResolvedTariff $tariff;
    public readonly int $roamingFeeCent;
    public readonly int $platformFeeCent;
    public readonly float $platformFeeReductionPercent;
    public readonly int $platformFeeEffectiveCent;
    public readonly int $paymentFeeCent;
    public readonly ?int $paymentFeeModelId;
    public readonly int $estimatedTotalPerKwhCent;
    public readonly array $transparencyBreakdown;

    public function __construct(
        ResolvedTariff $tariff,
        int $roamingFeeCent,
        int $platformFeeCent,
        float $platformFeeReductionPercent,
        int $platformFeeEffectiveCent,
        int $paymentFeeCent,
        ?int $paymentFeeModelId,
        int $estimatedTotalPerKwhCent,
        array $transparencyBreakdown = []
    ) {
        $this->tariff                      = $tariff;
        $this->roamingFeeCent              = $roamingFeeCent;
        $this->platformFeeCent             = $platformFeeCent;
        $this->platformFeeReductionPercent = $platformFeeReductionPercent;
        $this->platformFeeEffectiveCent    = $platformFeeEffectiveCent;
        $this->paymentFeeCent              = $paymentFeeCent;
        $this->paymentFeeModelId           = $paymentFeeModelId;
        $this->estimatedTotalPerKwhCent    = $estimatedTotalPerKwhCent;
        $this->transparencyBreakdown       = $transparencyBreakdown;
    }

    public function toSnapshotData(): array
    {
        return array_merge($this->tariff->toArray(), [
            'roaming_fee_cent'               => $this->roamingFeeCent,
            'platform_fee_cent'              => $this->platformFeeCent,
            'platform_fee_reduction_percent' => $this->platformFeeReductionPercent,
            'platform_fee_effective_cent'    => $this->platformFeeEffectiveCent,
            'payment_fee_cent'               => $this->paymentFeeCent,
            'payment_fee_model_id'           => $this->paymentFeeModelId,
            'estimated_total_per_kwh_cent'   => $this->estimatedTotalPerKwhCent,
            'transparency_json'              => json_encode($this->transparencyBreakdown),
        ]);
    }
}
