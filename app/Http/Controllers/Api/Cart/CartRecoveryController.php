<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Api\ApiController;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class CartRecoveryController extends ApiController
{
    public function show(string $token): JsonResponse
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()
            ->with('items.product', 'items.variant')
            ->where('recovery_token', $token)
            ->first();

        if (! $cart) {
            return $this->error('Recovery link is invalid.', 404);
        }

        if (! $cart->abandoned_at) {
            return $this->error('Cart is not marked as abandoned.', 422);
        }

        // validação simples de expiração: 7 dias após abandono
        if ($cart->abandoned_at->lt(Carbon::now()->subDays(7))) {
            return $this->error('Recovery link has expired.', 410);
        }

        return $this->success([
            'cart_public_id' => $cart->public_id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'product' => [
                        'public_id' => $item->product?->public_id,
                        'name' => $item->product?->name,
                    ],
                    'variant' => $item->variant ? [
                        'public_id' => $item->variant->public_id,
                        'name' => $item->variant->name,
                        'attributes' => $item->variant->attributes,
                    ] : null,
                ];
            }),
            'totals' => [
                'subtotal' => $cart->subtotal,
                'discount_total' => $cart->discount_total,
                'shipping_total' => $cart->shipping_total,
                'grand_total' => $cart->grand_total,
            ],
        ]);
    }
}

