<?php

namespace App\Domain\Logistics\Services;

use App\Models\ShippingMethod;
use App\Repositories\Eloquent\ShippingMethodRepository;
use Illuminate\Support\Collection;

class ShippingMethodService
{
    public function __construct(
        private readonly ShippingMethodRepository $repository,
    ) {
    }

    /**
     * @return Collection<int, ShippingMethod>
     */
    public function listActive(): Collection
    {
        /** @var Collection<int, ShippingMethod> $methods */
        $methods = collect($this->repository->listActive());

        return $methods;
    }
}

