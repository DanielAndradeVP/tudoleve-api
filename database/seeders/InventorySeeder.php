<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            Inventory::firstOrCreate(
                ['product_variant_id' => $variant->id],
                [
                    'public_id' => (string) Str::uuid(),
                    'product_id' => $variant->product_id,
                    'quantity' => 50,
                    'reserved_quantity' => 0,
                ],
            );
        }
    }
}

