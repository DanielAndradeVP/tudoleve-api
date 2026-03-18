<?php

namespace App\Domain\Cart\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

class CartService
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {
    }

    public function createCart(?int $customerId, ?string $sessionId): Cart
    {
        return Cart::query()->create([
            'public_id' => (string) Str::uuid(),
            'customer_id' => $customerId,
            'session_id' => $sessionId,
            'subtotal' => 0,
            'discount_total' => 0,
            'shipping_total' => 0,
            'grand_total' => 0,
            'last_activity_at' => now(),
        ]);
    }

    public function getCart(?int $customerId, ?string $sessionId): Cart
    {
        $query = Cart::query()->with('items.product', 'items.variant');

        if ($customerId !== null) {
            $query->where('customer_id', $customerId);
        } elseif ($sessionId !== null) {
            $query->where('session_id', $sessionId);
        } else {
            throw new \InvalidArgumentException('Either customerId or sessionId must be provided.');
        }

        /** @var Cart|null $cart */
        $cart = $query->first();

        if (! $cart) {
            $cart = $this->createCart($customerId, $sessionId);
            $cart->load('items.product', 'items.variant');
        } else {
            $cart->last_activity_at = now();
            $cart->save();
        }

        return $cart->fresh('items.product', 'items.variant');
    }

    public function addItem(Cart $cart, int $productId, ?int $productVariantId, int $quantity): Cart
    {
        return $this->db->transaction(function () use ($cart, $productId, $productVariantId, $quantity): Cart {
            /** @var Product $product */
            $product = Product::query()->findOrFail($productId);

            $unitPrice = $product->promotional_price ?? $product->price;

            if ($productVariantId !== null) {
                /** @var ProductVariant $variant */
                $variant = ProductVariant::query()
                    ->where('id', $productVariantId)
                    ->where('product_id', $productId)
                    ->firstOrFail();

                $unitPrice = $variant->price ?? $unitPrice;
            }

            /** @var CartItem|null $existingItem */
            $existingItem = $cart->items()
                ->where('product_id', $productId)
                ->where('product_variant_id', $productVariantId)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $quantity;
                $existingItem->total_price = $existingItem->quantity * $existingItem->unit_price;
                $existingItem->save();
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'product_variant_id' => $productVariantId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ]);
            }

            $this->recalculateTotals($cart);

            $cart->last_activity_at = now();
            $cart->save();

            return $cart->fresh('items.product', 'items.variant');
        });
    }

    public function removeItem(Cart $cart, int $itemId): Cart
    {
        return $this->db->transaction(function () use ($cart, $itemId): Cart {
            $item = $cart->items()->where('id', $itemId)->firstOrFail();
            $item->delete();

            $this->recalculateTotals($cart);

            $cart->last_activity_at = now();
            $cart->save();

            return $cart->fresh('items.product', 'items.variant');
        });
    }

    public function updateItemQuantity(Cart $cart, int $itemId, int $quantity): Cart
    {
        return $this->db->transaction(function () use ($cart, $itemId, $quantity): Cart {
            /** @var CartItem $item */
            $item = $cart->items()->where('id', $itemId)->firstOrFail();

            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->quantity = $quantity;
                $item->total_price = $item->unit_price * $quantity;
                $item->save();
            }

            $this->recalculateTotals($cart);

            $cart->last_activity_at = now();
            $cart->save();

            return $cart->fresh('items.product', 'items.variant');
        });
    }

    public function clearCart(Cart $cart): Cart
    {
        return $this->db->transaction(function () use ($cart): Cart {
            $cart->items()->delete();
            $cart->discount_total = 0;
            $cart->subtotal = 0;
            $cart->shipping_total = 0;
            $cart->grand_total = 0;
            $cart->last_activity_at = now();
            $cart->save();

            return $cart->fresh('items.product', 'items.variant');
        });
    }

    public function applyCoupon(Cart $cart, string $couponCode): Cart
    {
        return $this->db->transaction(function () use ($cart, $couponCode): Cart {
            /** @var Coupon $coupon */
            $coupon = Coupon::query()
                ->where('code', $couponCode)
                ->where('is_active', true)
                ->firstOrFail();

            $now = now();

            if (($coupon->starts_at && $coupon->starts_at->isAfter($now))
                || ($coupon->ends_at && $coupon->ends_at->isBefore($now))) {
                throw new \RuntimeException('Coupon is not valid at this time.');
            }

            $subtotal = $cart->items->sum('total_price');

            $discount = 0.0;

            if ($coupon->discount_type === 'fixed') {
                $discount = (float) $coupon->discount_value;
            } elseif ($coupon->discount_type === 'percent') {
                $discount = $subtotal * ((float) $coupon->discount_value / 100);
            }

            if ($coupon->max_discount_amount !== null) {
                $discount = min($discount, (float) $coupon->max_discount_amount);
            }

            $cart->discount_total = $discount;
            $this->recalculateTotals($cart);

            $cart->last_activity_at = now();
            $cart->save();

            return $cart->fresh('items.product', 'items.variant');
        });
    }

    public function removeCoupon(Cart $cart): Cart
    {
        $cart->discount_total = 0;
        $this->recalculateTotals($cart);

        $cart->last_activity_at = now();
        $cart->save();

        return $cart->fresh('items.product', 'items.variant');
    }

    public function recalculateTotals(Cart $cart): void
    {
        $subtotal = $cart->items()->sum('total_price');

        $cart->subtotal = $subtotal;
        $cart->grand_total = max(0, $subtotal - $cart->discount_total + $cart->shipping_total);
        $cart->save();
    }
}

