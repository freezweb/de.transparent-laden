<?php

namespace App\Libraries;

use App\Libraries\Entities\StructuredTariff;
use App\Libraries\Entities\ResolvedTariff;

class TariffResolver
{
    public function resolve(?StructuredTariff $tariff): ResolvedTariff
    {
        if (! $tariff) {
            return new ResolvedTariff(0, 0, 0, 0, 0, 0);
        }

        return ResolvedTariff::fromStructuredTariff($tariff);
    }
}
