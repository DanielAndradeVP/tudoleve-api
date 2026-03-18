<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Tudoleve',
            'Acme',
            'Genérico',
        ];

        foreach ($brands as $name) {
            Brand::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'public_id' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => $name,
                ],
            );
        }
    }
}

