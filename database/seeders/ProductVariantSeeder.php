<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(5)->get();

        foreach ($products as $product) {
            $variants = [
                [
                    'name' => $product->name . ' - P',
                    'attributes' => ['tamanho' => 'P'],
                ],
                [
                    'name' => $product->name . ' - M',
                    'attributes' => ['tamanho' => 'M'],
                ],
            ];

            foreach ($variants as $variantData) {
                ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'name' => $variantData['name'],
                    ],
                    [
                        'public_id' => (string) Str::uuid(),
                        'sku' => $product->sku . '-' . strtoupper(Str::random(3)),
                        'price' => $product->price,
                        'attributes' => $variantData['attributes'],
                    ],
                );
            }
        }
    }
}

