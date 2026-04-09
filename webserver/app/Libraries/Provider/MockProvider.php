<?php

namespace App\Libraries\Provider;

use App\Libraries\Entities\ProviderCapabilities;
use App\Libraries\Entities\StructuredTariff;

class MockProvider implements ProviderAdapterInterface
{
    private array $config;
    private array $sessions = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getCapabilities(): ProviderCapabilities
    {
        return new ProviderCapabilities([
            'start_session' => true,
            'stop_session'  => true,
            'live_status'   => true,
            'get_tariff'    => true,
            'location_sync' => true,
            'cdr'           => true,
        ]);
    }

    public function syncLocations(): array
    {
        return [
            [
                'external_id'    => 'MOCK-CP-001',
                'name'           => 'Mock Ladepunkt Berlin Mitte',
                'address'        => 'Friedrichstraße 100, 10117 Berlin',
                'city'           => 'Berlin',
                'postal_code'    => '10117',
                'latitude'       => 52.5200,
                'longitude'      => 13.4050,
                'operator_name'  => 'Mock Operator',
                'connectors'     => [
                    [
                        'external_id'    => 'MOCK-CON-001',
                        'connector_type' => 'CCS',
                        'power_kw'       => 150.0,
                        'status'         => 'available',
                        'tariff'         => [
                            'energy_price_per_kwh'  => 0.39,
                            'time_price_per_min'    => 0.00,
                            'blocking_fee_per_min'  => 0.10,
                            'blocking_free_minutes' => 240,
                            'start_fee'             => 0.00,
                            'min_billing_amount'    => 0.00,
                            'currency'              => 'EUR',
                        ],
                    ],
                    [
                        'external_id'    => 'MOCK-CON-002',
                        'connector_type' => 'Type2',
                        'power_kw'       => 22.0,
                        'status'         => 'available',
                        'tariff'         => [
                            'energy_price_per_kwh'  => 0.45,
                            'time_price_per_min'    => 0.02,
                            'blocking_fee_per_min'  => 0.05,
                            'blocking_free_minutes' => 180,
                            'start_fee'             => 1.00,
                            'min_billing_amount'    => 0.00,
                            'currency'              => 'EUR',
                        ],
                    ],
                ],
            ],
            [
                'external_id'    => 'MOCK-CP-002',
                'name'           => 'Mock Ladepunkt München Zentrum',
                'address'        => 'Marienplatz 1, 80331 München',
                'city'           => 'München',
                'postal_code'    => '80331',
                'latitude'       => 48.1374,
                'longitude'      => 11.5755,
                'operator_name'  => 'Mock Operator',
                'connectors'     => [
                    [
                        'external_id'    => 'MOCK-CON-003',
                        'connector_type' => 'CCS',
                        'power_kw'       => 300.0,
                        'status'         => 'available',
                        'tariff'         => [
                            'energy_price_per_kwh'  => 0.59,
                            'time_price_per_min'    => 0.00,
                            'blocking_fee_per_min'  => 0.15,
                            'blocking_free_minutes' => 60,
                            'start_fee'             => 0.00,
                            'min_billing_amount'    => 1.00,
                            'currency'              => 'EUR',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getTariff(string $connectorExternalId): ?StructuredTariff
    {
        $tariffs = [
            'MOCK-CON-001' => ['energy_price_per_kwh' => 0.39, 'blocking_fee_per_min' => 0.10, 'blocking_free_minutes' => 240, 'currency' => 'EUR'],
            'MOCK-CON-002' => ['energy_price_per_kwh' => 0.45, 'time_price_per_min' => 0.02, 'blocking_fee_per_min' => 0.05, 'blocking_free_minutes' => 180, 'start_fee' => 1.00, 'currency' => 'EUR'],
            'MOCK-CON-003' => ['energy_price_per_kwh' => 0.59, 'blocking_fee_per_min' => 0.15, 'blocking_free_minutes' => 60, 'min_billing_amount' => 1.00, 'currency' => 'EUR'],
        ];

        $data = $tariffs[$connectorExternalId] ?? null;
        return $data ? new StructuredTariff($data) : null;
    }

    public function startSession(string $connectorExternalId, string $tokenId): ?string
    {
        $sessionId = 'MOCK-SESSION-' . strtoupper(bin2hex(random_bytes(8)));
        $this->sessions[$sessionId] = [
            'connector' => $connectorExternalId,
            'status'    => 'active',
            'energy'    => 0.0,
            'started'   => time(),
        ];
        return $sessionId;
    }

    public function stopSession(string $sessionExternalId): bool
    {
        if (isset($this->sessions[$sessionExternalId])) {
            $this->sessions[$sessionExternalId]['status'] = 'completed';
            return true;
        }
        return true;
    }

    public function getSessionStatus(string $sessionExternalId): ?array
    {
        $elapsed = rand(60, 3600);
        $energyKwh = round($elapsed / 3600 * 22 * (rand(70, 95) / 100), 4);
        return [
            'status'     => 'active',
            'energy_kwh' => $energyKwh,
            'duration_s' => $elapsed,
            'power_kw'   => round(rand(5, 150) + rand(0, 99) / 100, 2),
        ];
    }
}
