<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            InventorySeeder::class,
            ShippingMethodSeeder::class,
            // opcionais
            CouponSeeder::class,
            BannerSeeder::class,
        ]);
    }
}

