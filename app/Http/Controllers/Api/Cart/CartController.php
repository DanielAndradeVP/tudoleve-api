<?php

namespace App\Http\Controllers\Api\Cart;

use App\Domain\Cart\Services\CartService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\ApplyCouponRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends ApiController
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    protected function resolveContext(Request $request): array
    {
        $customerId = $request->user()?->customer->id ?? null;
        $sessionId = $request->input('session_id') ?? $request->header('X-Cart-Session');

        if ($customerId === null && $sessionId === null) {
            return [null, null];
        }

        return [$customerId, $sessionId];
    }

    public function createSession(): JsonResponse
    {
        $sessionId = Str::uuid()->toString();

        return $this->success([
            'session_id' => $sessionId,
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        [$customerId, $sessionId] = $this->resolveContext($request);

        if ($customerId === null && $sessionId === null) {
            return $this->error('Customer or session identifier is required.', 422);
        }

        $cart = $this->cartService->getCart($customerId, $sessionId);

        return $this->success($cart);
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        [$customerId, $sessionId] = $this->resolveContext($request);

        if ($customerId === null && $sessionId === null) {
            return $this->error('Customer or session identifier is required.', 422);
        }

        $cart = $this->cartService->getCart($customerId, $sessionId);

        $data = $request->validated();

        $cart = $this->cartService->addItem(
            cart: $cart,
            productId: (int) $data['product_id'],
            productVariantId: $data['product_variant_id'] ?? null,
            quantity: (int) $data['quantity'],
        );

        return $this->success($cart);
    }

    public function updateItem(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        [$customerId, $sessionId] = $this->resolveContext($request);

        if ($customerId === null && $sessionId === null) {
            return $this->error('Customer or session identifier is required.', 422);
        }

        $cart = $this->cartService->getCart($customerId, $sessionId);

        $data = $request->validated();

        $cart = $this->cartService->updateItemQuantity(
            cart: $cart,
            itemId: $id,
            quantity: (int) $data['quantity'],
        );

        return $this->success($cart);
    }

    public function removeItem(Request $request, int $id): JsonResponse
    {
        [$customerId, $sessionId] = $this->resolveContext($request);

        if ($customerId === null && $sessionId === null) {
            return $this->error('Customer or session identifier is required.', 422);
        }

        $cart = $this->cartService->getCart($customerId, $sessionId);

        $cart = $this->cartService->removeItem($cart, $id);

        return $this->success($cart);
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        [$customerId, $sessionId] = $this->resolveContext($request);

        if ($customerId === null && $sessionId === null) {
            return $this->error('Customer or session identifier is required.', 422);
        }

        $cart = $this->cartService->getCart($customerId, $sessionId);

        $data = $request->validated();

        $cart = $this->cartService->applyCoupon(
            cart: $cart,
            couponCode: $data['coupon_code'],
        );

        return $this->success($cart);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        [$customerId, $sessionId] = $this->resolveContext($request);

        if ($customerId === null && $sessionId === null) {
            return $this->error('Customer or session identifier is required.', 422);
        }

        $cart = $this->cartService->getCart($customerId, $sessionId);

        $cart = $this->cartService->removeCoupon($cart);

        return $this->success($cart);
    }

    public function clear(Request $request): JsonResponse
    {
        [$customerId, $sessionId] = $this->resolveContext($request);

        if ($customerId === null && $sessionId === null) {
            return $this->error('Customer or session identifier is required.', 422);
        }

        $cart = $this->cartService->getCart($customerId, $sessionId);

        $cart = $this->cartService->clearCart($cart);

        return $this->success($cart);
    }
}

