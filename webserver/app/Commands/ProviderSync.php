<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Provider\ProviderFactory;
use App\Models\ProviderModel;
use App\Models\ChargePointModel;
use App\Models\ConnectorModel;

class ProviderSync extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'provider:sync';
    protected $description = 'Sync charge points and connectors from all active providers';

    public function run(array $params)
    {
        $providerModel   = model(ProviderModel::class);
        $chargePointModel = model(ChargePointModel::class);
        $connectorModel  = model(ConnectorModel::class);
        $factory         = new ProviderFactory();

        $providers = $providerModel->getActive();
        CLI::write('Found ' . count($providers) . ' active providers', 'green');

        foreach ($providers as $provider) {
            CLI::write("Syncing: {$provider['name']} ({$provider['slug']})", 'yellow');

            try {
                $adapter      = $factory->getAdapter($provider['slug']);
                $capabilities = $adapter->getCapabilities();

                if (! $capabilities->supportsLocationSync) {
                    CLI::write('  -> Skipped: no location sync support', 'light_gray');
                    continue;
                }

                $locations = $adapter->syncLocations();

                $created = 0;
                $updated = 0;

                foreach ($locations as $loc) {
                    $existing = $chargePointModel->findByProviderAndExternalId($provider['id'], $loc['external_id']);

                    if ($existing) {
                        $chargePointModel->update($existing['id'], [
                            'name'          => $loc['name'],
                            'address'       => $loc['address'] ?? null,
                            'city'          => $loc['city'] ?? null,
                            'postal_code'   => $loc['postal_code'] ?? null,
                            'country'       => $loc['country'] ?? 'DE',
                            'latitude'      => $loc['latitude'],
                            'longitude'     => $loc['longitude'],
                            'operator_name' => $loc['operator_name'] ?? null,
                            'is_active'     => 1,
                        ]);
                        $cpId = $existing['id'];
                        $updated++;
                    } else {
                        $cpId = $chargePointModel->insert([
                            'provider_id'   => $provider['id'],
                            'external_id'   => $loc['external_id'],
                            'name'          => $loc['name'],
                            'address'       => $loc['address'] ?? null,
                            'city'          => $loc['city'] ?? null,
                            'postal_code'   => $loc['postal_code'] ?? null,
                            'country'       => $loc['country'] ?? 'DE',
                            'latitude'      => $loc['latitude'],
                            'longitude'     => $loc['longitude'],
                            'operator_name' => $loc['operator_name'] ?? null,
                            'is_active'     => 1,
                        ]);
                        $created++;
                    }

                    if (! empty($loc['connectors'])) {
                        foreach ($loc['connectors'] as $conn) {
                            $existingConn = $connectorModel
                                ->where('charge_point_id', $cpId)
                                ->where('external_connector_id', $conn['external_connector_id'] ?? '')
                                ->first();

                            $connData = [
                                'charge_point_id'       => $cpId,
                                'external_connector_id' => $conn['external_connector_id'] ?? '',
                                'connector_type'        => $conn['connector_type'],
                                'power_kw'              => $conn['power_kw'],
                                'status'                => $conn['status'] ?? 'unknown',
                                'structured_tariff_json' => isset($conn['tariff']) ? json_encode($conn['tariff']) : null,
                            ];

                            if ($existingConn) {
                                $connectorModel->update($existingConn['id'], $connData);
                            } else {
                                $connectorModel->insert($connData);
                            }
                        }
                    }
                }

                CLI::write("  -> Created: {$created}, Updated: {$updated}", 'green');
            } catch (\Exception $e) {
                CLI::error("  -> Error: {$e->getMessage()}");
            }
        }

        CLI::write('Sync complete.', 'green');
    }
}
