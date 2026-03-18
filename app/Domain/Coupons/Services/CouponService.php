<?php

namespace App\Domain\Coupons\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use Illuminate\Database\DatabaseManager;

class CouponService
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {
    }

    public function validateCoupon(string $code, Cart $cart): Coupon
    {
        /** @var Coupon $coupon */
        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        $now = now();

        if (($coupon->starts_at && $coupon->starts_at->isAfter($now))
            || ($coupon->ends_at && $coupon->ends_at->isBefore($now))) {
            throw new \RuntimeException('Coupon is not valid at this time.');
        }

        if ($coupon->usage_limit !== null) {
            $totalUsage = CouponUsage::query()
                ->where('coupon_id', $coupon->id)
                ->count();

            if ($totalUsage >= $coupon->usage_limit) {
                throw new \RuntimeException('Coupon usage limit reached.');
            }
        }

        if ($coupon->usage_per_customer !== null && $cart->customer_id !== null) {
            $customerUsage = CouponUsage::query()
                ->where('coupon_id', $coupon->id)
                ->where('customer_id', $cart->customer_id)
                ->count();

            if ($customerUsage >= $coupon->usage_per_customer) {
                throw new \RuntimeException('Coupon usage limit reached for this customer.');
            }
        }

        return $coupon;
    }

    public function calculateDiscount(Cart $cart, Coupon $coupon): float
    {
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

        return max(0.0, $discount);
    }

    public function applyCoupon(Cart $cart, Coupon $coupon): Cart
    {
        return $this->db->transaction(function () use ($cart, $coupon): Cart {
            $discount = $this->calculateDiscount($cart, $coupon);

            $cart->discount_total = $discount;
            $cart->save();

            return $cart->fresh('items.product', 'items.variant');
        });
    }

    public function registerCouponUsage(Order $order, Coupon $coupon): void
    {
        if ($order->customer_id === null) {
            return;
        }

        CouponUsage::query()->create([
            'coupon_id' => $coupon->id,
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
        ]);
    }
}

