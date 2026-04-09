<?php

namespace App\Libraries\Entities;

/**
 * Immutable structured tariff from provider (raw OCPI/OICP format).
 */
class StructuredTariff
{
    public readonly float $energyPricePerKwh;
    public readonly float $timePricePerMin;
    public readonly float $blockingFeePerMin;
    public readonly int $blockingFreeMinutes;
    public readonly float $startFee;
    public readonly float $minBillingAmount;
    public readonly ?string $currency;
    public readonly array $rawComponents;

    public function __construct(array $data)
    {
        $this->energyPricePerKwh  = (float) ($data['energy_price_per_kwh'] ?? 0);
        $this->timePricePerMin    = (float) ($data['time_price_per_min'] ?? 0);
        $this->blockingFeePerMin  = (float) ($data['blocking_fee_per_min'] ?? 0);
        $this->blockingFreeMinutes = (int) ($data['blocking_free_minutes'] ?? 0);
        $this->startFee           = (float) ($data['start_fee'] ?? 0);
        $this->minBillingAmount   = (float) ($data['min_billing_amount'] ?? 0);
        $this->currency           = $data['currency'] ?? 'EUR';
        $this->rawComponents      = $data['components'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'energy_price_per_kwh'  => $this->energyPricePerKwh,
            'time_price_per_min'    => $this->timePricePerMin,
            'blocking_fee_per_min'  => $this->blockingFeePerMin,
            'blocking_free_minutes' => $this->blockingFreeMinutes,
            'start_fee'             => $this->startFee,
            'min_billing_amount'    => $this->minBillingAmount,
            'currency'              => $this->currency,
        ];
    }

    public static function fromJson(?string $json): ?self
    {
        if (empty($json)) {
            return null;
        }
        $data = json_decode($json, true);
        return $data ? new self($data) : null;
    }
}
