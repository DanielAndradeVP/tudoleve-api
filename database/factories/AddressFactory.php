<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'customer_id' => Customer::factory(),
            'label' => 'Principal',
            'recipient_name' => $this->faker->name(),
            'street' => $this->faker->streetName(),
            'number' => (string) $this->faker->buildingNumber(),
            'complement' => null,
            'district' => $this->faker->citySuffix(),
            'city' => $this->faker->city(),
            'state' => 'SP',
            'postal_code' => $this->faker->postcode(),
            'country' => 'BR',
            'is_default' => true,
        ];
    }
}

