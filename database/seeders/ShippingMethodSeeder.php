<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'PAC',
                'code' => 'PAC',
                'base_cost' => 20.00,
                'estimated_min_days' => 5,
                'estimated_max_days' => 10,
            ],
            [
                'name' => 'SEDEX',
                'code' => 'SEDEX',
                'base_cost' => 35.00,
                'estimated_min_days' => 2,
                'estimated_max_days' => 5,
            ],
            [
                'name' => 'Transportadora',
                'code' => 'TRANSPORTADORA',
                'base_cost' => 25.00,
                'estimated_min_days' => 3,
                'estimated_max_days' => 7,
            ],
        ];

        foreach ($methods as $data) {
            ShippingMethod::firstOrCreate(
                ['code' => $data['code']],
                [
                    'public_id' => (string) Str::uuid(),
                    'name' => $data['name'],
                    'is_active' => true,
                    'base_cost' => $data['base_cost'],
                    'estimated_min_days' => $data['estimated_min_days'],
                    'estimated_max_days' => $data['estimated_max_days'],
                    'configuration' => [],
                ],
            );
        }
    }
}

