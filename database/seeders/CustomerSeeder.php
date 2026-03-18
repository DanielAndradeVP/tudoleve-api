<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'public_id' => (string) Str::uuid(),
                'name' => 'Cliente Exemplo',
                'password' => 'password',
            ],
        );

        Customer::firstOrCreate(
            ['email' => $user->email],
            [
                'public_id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => '+5511999999999',
            ],
        );
    }
}

