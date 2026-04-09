<?php

namespace App\Libraries\Entities;

/**
 * Provider capabilities descriptor.
 */
class ProviderCapabilities
{
    public readonly bool $supportsStartSession;
    public readonly bool $supportsStopSession;
    public readonly bool $supportsLiveStatus;
    public readonly bool $supportsGetTariff;
    public readonly bool $supportsLocationSync;
    public readonly bool $supportsCdr;

    public function __construct(array $capabilities = [])
    {
        $this->supportsStartSession = (bool) ($capabilities['start_session'] ?? false);
        $this->supportsStopSession  = (bool) ($capabilities['stop_session'] ?? false);
        $this->supportsLiveStatus   = (bool) ($capabilities['live_status'] ?? false);
        $this->supportsGetTariff    = (bool) ($capabilities['get_tariff'] ?? false);
        $this->supportsLocationSync = (bool) ($capabilities['location_sync'] ?? false);
        $this->supportsCdr          = (bool) ($capabilities['cdr'] ?? false);
    }

    public function toArray(): array
    {
        return [
            'start_session' => $this->supportsStartSession,
            'stop_session'  => $this->supportsStopSession,
            'live_status'   => $this->supportsLiveStatus,
            'get_tariff'    => $this->supportsGetTariff,
            'location_sync' => $this->supportsLocationSync,
            'cdr'           => $this->supportsCdr,
        ];
    }
}
