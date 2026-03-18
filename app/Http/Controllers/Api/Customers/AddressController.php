<?php

namespace App\Http\Controllers\Api\Customers;

use App\Domain\Customer\Services\AddressService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class AddressController extends ApiController
{
    public function __construct(
        private readonly AddressService $addressService,
    ) {
    }

    public function index(Customer $customer)
    {
        $addresses = $this->addressService->listForCustomer($customer);

        return $this->success($addresses);
    }

    public function store(StoreAddressRequest $request, Customer $customer)
    {
        $address = $this->addressService->createForCustomer($customer, $request->validated());

        return $this->success($address, 201);
    }

    public function show(Customer $customer, string $publicId)
    {
        $address = $this->addressService->findForCustomer($customer, $publicId);

        return $this->success($address);
    }

    public function update(UpdateAddressRequest $request, Customer $customer, string $publicId)
    {
        $address = $this->addressService->findForCustomer($customer, $publicId);

        $updated = $this->addressService->update($address, $request->validated());

        return $this->success($updated);
    }

    public function destroy(Customer $customer, string $publicId)
    {
        $address = $this->addressService->findForCustomer($customer, $publicId);

        $this->addressService->delete($address);

        return $this->success(null, 204);
    }
}

