<?php

namespace App\Http\Controllers\Api\Logistics;

use App\Domain\Logistics\Services\ShippingMethodService;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;

class ShippingMethodController extends ApiController
{
    public function __construct(
        private readonly ShippingMethodService $shippingMethodService,
    ) {
    }

    public function index(): JsonResponse
    {
        $methods = $this->shippingMethodService->listActive()
            ->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'price' => (float) $method->base_cost,
                    'estimated_days' => [
                        'min' => $method->estimated_min_days,
                        'max' => $method->estimated_max_days,
                    ],
                    'active' => (bool) $method->is_active,
                ];
            });

        return $this->success($methods);
    }
}

