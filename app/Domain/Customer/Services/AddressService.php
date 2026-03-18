<?php

namespace App\Domain\Customer\Services;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class AddressService
{
    /**
     * @return Collection<int, Address>
     */
    public function listForCustomer(Customer $customer): Collection
    {
        return $customer->addresses()->get();
    }

    public function findForCustomer(Customer $customer, string $publicId): Address
    {
        /** @var Address|null $address */
        $address = $customer->addresses()
            ->where('public_id', $publicId)
            ->first();

        if (! $address) {
            throw new ModelNotFoundException('Address not found.');
        }

        return $address;
    }

    public function createForCustomer(Customer $customer, array $data): Address
    {
        /** @var Address $address */
        $address = $customer->addresses()->create([
            'public_id' => (string) Str::uuid(),
            'label' => $data['label'] ?? null,
            'recipient_name' => $data['recipient_name'],
            'street' => $data['street'],
            'number' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'district' => $data['district'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'] ?? 'BR',
            'is_default' => $data['is_default'] ?? false,
        ]);

        if ($address->is_default) {
            $this->setDefault($customer, $address);
        }

        return $address;
    }

    public function update(Address $address, array $data): Address
    {
        $address->fill($data);
        $address->save();

        if (! empty($data['is_default']) && $data['is_default']) {
            $this->setDefault($address->customer, $address);
        }

        return $address->fresh();
    }

    public function delete(Address $address): void
    {
        $address->delete();
    }

    private function setDefault(?Customer $customer, Address $defaultAddress): void
    {
        if (! $customer) {
            return;
        }

        $customer->addresses()
            ->where('id', '!=', $defaultAddress->id)
            ->update(['is_default' => false]);

        $defaultAddress->is_default = true;
        $defaultAddress->save();
    }
}

