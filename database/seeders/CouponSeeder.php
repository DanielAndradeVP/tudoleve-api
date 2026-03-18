<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'BEMVINDO10'],
            [
                'public_id' => (string) Str::uuid(),
                'discount_type' => 'percent',
                'discount_value' => 10,
                'max_discount_amount' => 100,
                'starts_at' => Carbon::now()->subDay(),
                'ends_at' => Carbon::now()->addMonths(6),
                'usage_limit' => 1000,
                'usage_per_customer' => 1,
                'is_active' => true,
            ],
        );
    }
}

