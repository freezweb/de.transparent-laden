<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run()
    {
        $configs = [
            ['config_key' => 'platform_fee_cent_per_kwh', 'config_value' => '3', 'description' => 'Plattformgebühr in Cent pro kWh'],
            ['config_key' => 'vat_rate', 'config_value' => '19', 'description' => 'MwSt-Satz in Prozent'],
            ['config_key' => 'blocking_fee_cent_per_min', 'config_value' => '10', 'description' => 'Blockiergebühr in Cent pro Minute'],
            ['config_key' => 'blocking_free_minutes', 'config_value' => '15', 'description' => 'Freiminuten nach Ladeende bevor Blockiergebühr greift'],
            ['config_key' => 'blocking_warning_minutes', 'config_value' => '10', 'description' => 'Minuten vor Blockiergebühr-Warnung'],
            ['config_key' => 'min_billing_amount_cent', 'config_value' => '100', 'description' => 'Mindest-Rechnungsbetrag in Cent'],
            ['config_key' => 'stale_session_minutes', 'config_value' => '120', 'description' => 'Minuten ohne Update bis Session als stale gilt'],
            ['config_key' => 'max_recovery_attempts', 'config_value' => '3', 'description' => 'Maximale Recovery-Versuche für stuck Sessions'],
            ['config_key' => 'lexware_enabled', 'config_value' => '0', 'description' => 'Lexware Office Integration aktiv'],
            ['config_key' => 'fcm_enabled', 'config_value' => '0', 'description' => 'Firebase Cloud Messaging aktiv'],
            ['config_key' => 'mock_provider_enabled', 'config_value' => '1', 'description' => 'Mock Provider für Entwicklung aktiv'],
            ['config_key' => 'default_search_radius_km', 'config_value' => '10', 'description' => 'Standard-Suchradius für Ladepunkte in km'],
            ['config_key' => 'invoice_prefix', 'config_value' => 'EL', 'description' => 'Rechnungsnummer-Präfix'],
        ];

        foreach ($configs as $config) {
            $this->db->table('system_config')->insert(array_merge($config, [
                'updated_by' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }
    }
}
