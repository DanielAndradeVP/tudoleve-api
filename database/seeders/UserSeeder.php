<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'public_id' => (string) Str::uuid(),
                'name' => 'Admin',
                'password' => 'password',
            ],
        );
    }
}

