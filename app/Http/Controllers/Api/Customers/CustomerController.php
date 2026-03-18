<?php

namespace App\Http\Controllers\Api\Customers;

use App\Domain\Customer\Services\CustomerService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function __construct(
        private readonly CustomerService $customerService,
    ) {
    }

    public function index(Request $request)
    {
        $customers = $this->customerService->list(
            filters: [
                'search' => $request->query('search'),
            ],
            perPage: (int) $request->query('per_page', 15),
        );

        return $this->success($customers);
    }

    public function show(string $publicId)
    {
        $customer = $this->customerService->findByPublicId($publicId);

        return $this->success($customer);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->create($request->validated());

        return $this->success($customer, 201);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $updated = $this->customerService->update($customer, $request->validated());

        return $this->success($updated);
    }

    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);

        return $this->success(null, 204);
    }
}

