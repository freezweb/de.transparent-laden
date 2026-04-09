<?php

namespace App\Libraries\Entities;

/**
 * Resolved tariff with all fee components calculated (immutable).
 */
class ResolvedTariff
{
    public readonly int $energyPricePerKwhCent;
    public readonly int $timePricePerMinCent;
    public readonly int $blockingFeePerMinCent;
    public readonly int $blockingFreeMinutes;
    public readonly int $startFeeCent;
    public readonly int $minBillingAmountCent;

    public function __construct(
        int $energyPricePerKwhCent,
        int $timePricePerMinCent,
        int $blockingFeePerMinCent,
        int $blockingFreeMinutes,
        int $startFeeCent,
        int $minBillingAmountCent
    ) {
        $this->energyPricePerKwhCent  = $energyPricePerKwhCent;
        $this->timePricePerMinCent    = $timePricePerMinCent;
        $this->blockingFeePerMinCent  = $blockingFeePerMinCent;
        $this->blockingFreeMinutes    = $blockingFreeMinutes;
        $this->startFeeCent           = $startFeeCent;
        $this->minBillingAmountCent   = $minBillingAmountCent;
    }

    public static function fromStructuredTariff(StructuredTariff $tariff): self
    {
        return new self(
            (int) round($tariff->energyPricePerKwh * 100),
            (int) round($tariff->timePricePerMin * 100),
            (int) round($tariff->blockingFeePerMin * 100),
            $tariff->blockingFreeMinutes,
            (int) round($tariff->startFee * 100),
            (int) round($tariff->minBillingAmount * 100),
        );
    }

    public function toArray(): array
    {
        return [
            'energy_price_per_kwh_cent'  => $this->energyPricePerKwhCent,
            'time_price_per_min_cent'    => $this->timePricePerMinCent,
            'blocking_fee_per_min_cent'  => $this->blockingFeePerMinCent,
            'blocking_free_minutes'      => $this->blockingFreeMinutes,
            'start_fee_cent'             => $this->startFeeCent,
            'min_billing_amount_cent'    => $this->minBillingAmountCent,
        ];
    }
}
