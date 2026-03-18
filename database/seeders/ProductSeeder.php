<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::take(3)->get();
        $brands = Brand::take(3)->get();

        if ($categories->count() === 0 || $brands->count() === 0) {
            return;
        }

        $definitions = [
            ['name' => 'Cafeteira Elétrica 110V', 'price' => 199.90],
            ['name' => 'Cadeira Ergonômica', 'price' => 899.90],
            ['name' => 'Monitor 24" Full HD', 'price' => 799.90],
            ['name' => 'Kit Panelas Antiaderentes', 'price' => 299.90],
            ['name' => 'Teclado Mecânico', 'price' => 349.90],
        ];

        foreach ($definitions as $index => $data) {
            $category = $categories[$index % $categories->count()];
            $brand = $brands[$index % $brands->count()];

            Product::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'public_id' => (string) Str::uuid(),
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $data['name'],
                    'sku' => 'SKU-' . strtoupper(Str::random(6)),
                    'description' => $data['name'],
                    'is_active' => true,
                    'price' => $data['price'],
                    'promotional_price' => $data['price'] * 0.9,
                ],
            );
        }
    }
}

