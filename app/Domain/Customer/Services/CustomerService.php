<?php

namespace App\Domain\Customer\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class CustomerService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Customer::query()->with('user');

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function findByPublicId(string $publicId): Customer
    {
        /** @var Customer|null $customer */
        $customer = Customer::query()
            ->with(['user', 'addresses'])
            ->where('public_id', $publicId)
            ->first();

        if (! $customer) {
            throw new ModelNotFoundException('Customer not found.');
        }

        return $customer;
    }

    public function create(array $data): Customer
    {
        $publicId = (string) Str::uuid();

        /** @var User $user */
        $user = User::query()->create([
            'public_id' => $publicId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? Str::random(16),
        ]);

        /** @var Customer $customer */
        $customer = Customer::query()->create([
            'public_id' => $publicId,
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        return $customer->fresh(['user', 'addresses']);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->fill([
            'name' => $data['name'] ?? $customer->name,
            'email' => $data['email'] ?? $customer->email,
            'phone' => $data['phone'] ?? $customer->phone,
        ]);

        $customer->save();

        if ($customer->user) {
            $customer->user->fill([
                'name' => $customer->name,
                'email' => $customer->email,
            ]);

            if (! empty($data['password'])) {
                $customer->user->password = $data['password'];
            }

            $customer->user->save();
        }

        return $customer->fresh(['user', 'addresses']);
    }

    public function delete(Customer $customer): void
    {
        if ($customer->user) {
            $customer->user->delete();
        }

        $customer->delete();
    }

    /**
     * @return Collection<int, Customer>
     */
    public function listForUser(User $user): Collection
    {
        return Customer::query()
            ->where('user_id', $user->id)
            ->with('addresses')
            ->get();
    }
}

