<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Domain\Checkout\Services\CheckoutService;
use App\Domain\Checkout\Services\QuickCheckoutService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Requests\Checkout\QuickCheckoutRequest;

class CheckoutController extends ApiController
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly QuickCheckoutService $quickCheckoutService,
    ) {
    }

    public function store(CheckoutRequest $request)
    {
        $result = $this->checkoutService->checkout($request->validated());

        return $this->success([
            'order' => $result['order'],
            'payment' => $result['payment'],
            'shipping' => $result['shipping'],
            'gateway' => $result['gateway'],
        ]);
    }

    public function quick(QuickCheckoutRequest $request)
    {
        $result = $this->quickCheckoutService->checkout($request->validated());

        return $this->success([
            'order' => $result['order'],
            'payment' => $result['payment'],
            'shipping' => $result['shipping'],
            'gateway' => $result['gateway'],
        ]);
    }
}

