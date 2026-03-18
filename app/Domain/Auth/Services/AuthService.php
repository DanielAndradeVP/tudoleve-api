<?php

namespace App\Domain\Auth\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function login(string $email, string $password): array
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $token = $user->createToken('auth_token');

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function logout(Authenticatable&User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }
    }

    public function registerCustomer(array $data): array
    {
        $publicId = (string) Str::uuid();

        /** @var User $user */
        $user = User::query()->create([
            'public_id' => $publicId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        /** @var Customer $customer */
        $customer = Customer::query()->create([
            'public_id' => $publicId,
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        $token = $user->createToken('auth_token');

        return [
            'user' => $user,
            'customer' => $customer,
            'token' => $token->plainTextToken,
        ];
    }

    public function refreshToken(Authenticatable&User $user): string
    {
        $user->currentAccessToken()?->delete();

        $token = $user->createToken('auth_token');

        return $token->plainTextToken;
    }
}

