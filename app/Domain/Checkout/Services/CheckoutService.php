<?php

namespace App\Domain\Checkout\Services;

use App\Domain\Coupons\Services\CouponService;
use App\Domain\Logistics\DataTransferObjects\FreightQuoteRequestData;
use App\Domain\Logistics\Services\LogisticsService;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Enums\PaymentMethod;
use App\Domain\Payments\Services\PaymentService;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly LogisticsService $logisticsService,
        private readonly PaymentService $paymentService,
        private readonly CouponService $couponService,
    ) {
    }

    /**
     * Executes the checkout flow: turns a cart into an order, calculates
     * shipping, and initializes the payment.
     *
     * @return array{
     *     order: Order,
     *     payment: \App\Models\Payment,
     *     shipping: array<mixed>,
     *     gateway: array<mixed>
     * }
     */
    public function checkout(array $data): array
    {
        return $this->db->transaction(function () use ($data): array {
            /** @var Cart $cart */
            $cart = Cart::query()
                ->with('items.product')
                ->where('public_id', $data['cart_public_id'])
                ->firstOrFail();

            $appliedCoupon = null;

            if (! empty($data['coupon_code'] ?? null)) {
                $appliedCoupon = $this->couponService->validateCoupon($data['coupon_code'], $cart);
                $discount = $this->couponService->calculateDiscount($cart, $appliedCoupon);

                $cart->discount_total = $discount;
                $cart->grand_total = max(0, $cart->subtotal - $cart->discount_total);
                $cart->save();
            }

            /** @var Address $shippingAddress */
            $shippingAddress = Address::query()
                ->where('id', $data['shipping_address_id'])
                ->where('customer_id', $cart->customer_id)
                ->firstOrFail();

            /** @var Address $billingAddress */
            $billingAddress = Address::query()
                ->where('id', $data['billing_address_id'])
                ->where('customer_id', $cart->customer_id)
                ->firstOrFail();

            $shippingMethod = ShippingMethod::query()
                ->where('id', $data['shipping_method_id'])
                ->where('is_active', true)
                ->firstOrFail();

            // Very simplified shipping calculation using logistics layer.
            $totalQuantity = $cart->items->sum('quantity');

            $freightRequest = new FreightQuoteRequestData(
                originPostalCode: config('app.origin_postal_code', '01000-000'),
                destinationPostalCode: $shippingAddress->postal_code,
                weightKg: max(1, $totalQuantity),
                volumeM3: 0.01 * $totalQuantity,
                declaredValue: $cart->grand_total,
            );

            $freightQuote = $this->logisticsService->quoteShipping($freightRequest);

            $order = new Order([
                'public_id' => (string) Str::uuid(),
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $cart->customer_id,
                'subtotal' => $cart->subtotal,
                'discount_total' => $cart->discount_total,
                'shipping_total' => $freightQuote->totalCost,
                'grand_total' => $cart->subtotal - $cart->discount_total + $freightQuote->totalCost,
                'quoted_shipping_cost' => $freightQuote->totalCost,
                'currency' => 'BRL',
                'status' => OrderStatus::PENDING->value,
                'payment_status' => 'pending',
            ]);

            $order->billingAddress()->associate($billingAddress);
            $order->shippingAddress()->associate($shippingAddress);
            $order->save();

            foreach ($cart->items as $item) {
                $orderItem = new OrderItem([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);

                $orderItem->order()->associate($order);
                $orderItem->save();
            }

            $shipment = new Shipment([
                'public_id' => (string) Str::uuid(),
                'shipping_cost' => $freightQuote->totalCost,
            ]);

            $shipment->order()->associate($order);
            $shipment->shippingMethod()->associate($shippingMethod);
            $shipment->save();

            $paymentMethod = PaymentMethod::from($data['payment_method']);

            $paymentResult = $this->paymentService->createPaymentForOrder(
                order: $order,
                method: $paymentMethod,
                provider: $data['payment_provider'] ?? 'local',
            );

            // Mark cart as soft-deleted to represent checkout completion.
            $cart->delete();

            if ($appliedCoupon !== null) {
                $this->couponService->registerCouponUsage($order, $appliedCoupon);
            }

            return [
                'order' => $paymentResult['order'],
                'payment' => $paymentResult['payment'],
                'shipping' => [
                    'total' => $freightQuote->totalCost,
                    'currency' => $freightQuote->currency,
                    'estimated_delivery_date' => $freightQuote->estimatedDeliveryDate->format(DATE_ATOM),
                    'breakdown' => $freightQuote->breakdown,
                    'shipping_method' => [
                        'id' => $shippingMethod->id,
                        'name' => $shippingMethod->name,
                        'code' => $shippingMethod->code,
                    ],
                ],
                'gateway' => $paymentResult['gateway'],
            ];
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
    }
}

