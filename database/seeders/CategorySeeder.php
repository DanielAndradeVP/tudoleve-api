<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Eletrônicos',
            'Casa & Cozinha',
            'Escritório',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
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

