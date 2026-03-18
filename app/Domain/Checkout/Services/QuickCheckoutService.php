<?php

namespace App\Domain\Checkout\Services;

use App\Domain\Cart\Services\CartService;
use App\Models\Address;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Auth;

class QuickCheckoutService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
    ) {
    }

    /**
     * @return array{
     *     order: \App\Models\Order,
     *     payment: \App\Models\Payment,
     *     shipping: array<mixed>,
     *     gateway: array<mixed>
     * }
     */
    public function checkout(array $data): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->customer) {
            throw new \RuntimeException('Quick checkout requires an authenticated customer.');
        }

        return $this->db->transaction(function () use ($data, $user): array {
            $customer = $user->customer;

            /** @var ProductVariant $variant */
            $variant = ProductVariant::query()
                ->with('product')
                ->findOrFail($data['product_variant_id']);

            /** @var Cart $cart */
            $cart = $this->cartService->createCart($customer->id, null);

            $this->cartService->addItem(
                cart: $cart,
                productId: $variant->product_id,
                productVariantId: $variant->id,
                quantity: (int) $data['quantity'],
            );

            /** @var Address|null $latestAddress */
            $latestAddress = $customer->addresses()
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            if (! $latestAddress) {
                throw new \RuntimeException('Customer does not have any address configured for quick checkout.');
            }

            $checkoutPayload = [
                'cart_public_id' => $cart->public_id,
                'billing_address_id' => $latestAddress->id,
                'shipping_address_id' => $latestAddress->id,
                'shipping_method_id' => $data['shipping_method_id'],
                'payment_method' => $data['payment_method'],
                'payment_provider' => $data['payment_provider'] ?? 'local',
            ];

            return $this->checkoutService->checkout($checkoutPayload);
        });
    }
}

