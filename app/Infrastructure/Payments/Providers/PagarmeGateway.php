<?php

namespace App\Infrastructure\Payments\Providers;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;

class PagarmeGateway implements PaymentGatewayInterface
{
    public function createPayment(Payment $payment, array $options = []): array
    {
        $metadata = match ($payment->method) {
            'pix' => [
                'qr_code' => '0002010102122688...FAKE_PAGARME_PIX_QR_CODE...',
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
            ],
            'credit_card' => [
                'client_secret' => 'pagarme_client_secret_' . $payment->public_id,
            ],
            'boleto' => [
                'boleto_url' => 'https://example.test/pagarme/boleto/' . $payment->public_id,
                'expires_at' => now()->addDays(3)->toIso8601String(),
            ],
            default => [],
        };

        $externalReference = 'pg_' . $payment->public_id;

        $payment->update([
            'provider' => 'pagarme',
            'external_reference' => $externalReference,
            'status' => PaymentStatus::PENDING->value,
            'metadata' => array_merge($payment->metadata ?? [], $metadata),
        ]);

        return [
            'provider' => 'pagarme',
            'method' => $payment->method,
            'external_reference' => $externalReference,
            'metadata' => $metadata,
        ];
    }

    public function handleWebhook(array $payload): ?Payment
    {
        $externalReference = $payload['external_reference'] ?? null;

        if (! $externalReference) {
            return null;
        }

        /** @var Payment|null $payment */
        $payment = Payment::query()
            ->where('external_reference', $externalReference)
            ->first();

        if (! $payment) {
            return null;
        }

        $status = $payload['status'] ?? PaymentStatus::PENDING->value;

        $payment->status = $status;
        $payment->save();

        /** @var Order $order */
        $order = $payment->order;

        if ($status === PaymentStatus::PAID->value) {
            $order->payment_status = PaymentStatus::PAID->value;
            $order->status = OrderStatus::PROCESSING->value;
        } elseif (in_array($status, [PaymentStatus::CANCELLED->value, PaymentStatus::REFUNDED->value], true)) {
            $order->payment_status = $status;
            $order->status = OrderStatus::CANCELLED->value;
        }

        $order->save();

        return $payment;
    }

    public function capture(Payment $payment, ?float $amount = null): void
    {
        $payment->status = PaymentStatus::PAID->value;
        $payment->save();

        /** @var Order $order */
        $order = $payment->order;
        $order->payment_status = PaymentStatus::PAID->value;
        $order->status = OrderStatus::PROCESSING->value;
        $order->save();
    }

    public function cancel(Payment $payment): void
    {
        $payment->status = PaymentStatus::CANCELLED->value;
        $payment->save();

        /** @var Order $order */
        $order = $payment->order;
        $order->payment_status = PaymentStatus::CANCELLED->value;
        $order->status = OrderStatus::CANCELLED->value;
        $order->save();
    }

    public function refund(Payment $payment, float $amount): void
    {
        $payment->status = PaymentStatus::REFUNDED->value;
        $payment->save();

        /** @var Order $order */
        $order = $payment->order;
        $order->payment_status = PaymentStatus::REFUNDED->value;
        $order->status = OrderStatus::CANCELLED->value;
        $order->save();
    }
}

