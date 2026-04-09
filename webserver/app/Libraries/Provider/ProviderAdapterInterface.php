<?php

namespace App\Libraries\Provider;

use App\Libraries\Entities\ProviderCapabilities;
use App\Libraries\Entities\StructuredTariff;

interface ProviderAdapterInterface
{
    public function getCapabilities(): ProviderCapabilities;

    public function syncLocations(): array;

    public function getTariff(string $connectorExternalId): ?StructuredTariff;

    public function startSession(string $connectorExternalId, string $tokenId): ?string;

    public function stopSession(string $sessionExternalId): bool;

    public function getSessionStatus(string $sessionExternalId): ?array;
}
