<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name'                          => 'Free',
                'slug'                          => 'free',
                'description'                   => 'Kostenloser Zugang mit Standard-Plattformgebühr',
                'price_monthly_cent'            => 0,
                'price_yearly_cent'             => 0,
                'platform_fee_reduction_percent' => 0,
                'features_json'                 => json_encode([
                    'transparent_pricing'  => true,
                    'charging_history'     => true,
                    'push_notifications'   => true,
                    'priority_support'     => false,
                    'detailed_analytics'   => false,
                    'multi_vehicle'        => false,
                ]),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'                          => 'Premium',
                'slug'                          => 'premium',
                'description'                   => 'Reduzierte Plattformgebühr und erweiterte Features',
                'price_monthly_cent'            => 499,
                'price_yearly_cent'             => 4990,
                'platform_fee_reduction_percent' => 50,
                'features_json'                 => json_encode([
                    'transparent_pricing'  => true,
                    'charging_history'     => true,
                    'push_notifications'   => true,
                    'priority_support'     => true,
                    'detailed_analytics'   => true,
                    'multi_vehicle'        => false,
                ]),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'                          => 'Business',
                'slug'                          => 'business',
                'description'                   => 'Keine Plattformgebühr, alle Features, Flottenmanagement',
                'price_monthly_cent'            => 1499,
                'price_yearly_cent'             => 14990,
                'platform_fee_reduction_percent' => 100,
                'features_json'                 => json_encode([
                    'transparent_pricing'  => true,
                    'charging_history'     => true,
                    'push_notifications'   => true,
                    'priority_support'     => true,
                    'detailed_analytics'   => true,
                    'multi_vehicle'        => true,
                    'fleet_management'     => true,
                    'api_access'           => true,
                ]),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($plans as $plan) {
            $this->db->table('subscription_plans')->insert($plan);
            $planId = $this->db->insertID();

            $this->db->table('subscription_plan_versions')->insert([
                'plan_id'                        => $planId,
                'version'                        => 1,
                'price_monthly_cent'             => $plan['price_monthly_cent'],
                'price_yearly_cent'              => $plan['price_yearly_cent'],
                'platform_fee_reduction_percent' => $plan['platform_fee_reduction_percent'],
                'features_json'                  => $plan['features_json'],
                'valid_from'                     => date('Y-m-d H:i:s'),
                'valid_until'                    => null,
                'created_at'                     => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
