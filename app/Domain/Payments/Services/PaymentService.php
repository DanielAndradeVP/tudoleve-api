<?php

namespace App\Domain\Payments\Services;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Enums\PaymentMethod;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayFactory $gatewayFactory,
    ) {
    }

    public function createPaymentForOrder(
        Order $order,
        PaymentMethod $method,
        ?string $provider = null,
        ?float $amount = null,
        array $options = []
    ): array {
        $payment = new Payment([
            'public_id' => (string) Str::uuid(),
            'amount' => $amount ?? $order->grand_total,
            'currency' => $order->currency,
            'method' => $method->value,
            'status' => PaymentStatus::PENDING->value,
            'provider' => $provider,
        ]);

        $payment->order()->associate($order);
        $payment->save();

        $gateway = $this->gatewayFactory->resolve($provider);
        $gatewayResponse = $gateway->createPayment($payment, $options);

        $this->logTransaction(
            payment: $payment,
            type: 'authorization',
            amount: $payment->amount,
            status: PaymentStatus::PENDING,
            externalReference: $gatewayResponse['external_reference'] ?? null,
            payload: $gatewayResponse,
        );

        return [
            'order' => $order->fresh(['items', 'payments']),
            'payment' => $payment->fresh(),
            'gateway' => $gatewayResponse,
        ];
    }

    public function handleWebhook(string $provider, array $payload): ?Payment
    {
        $gateway = $this->gatewayFactory->resolve($provider);
        $payment = $gateway->handleWebhook($payload);

        if (! $payment) {
            return null;
        }

        $status = PaymentStatus::from($payment->status);

        $this->logTransaction(
            payment: $payment,
            type: 'webhook',
            amount: $payment->amount,
            status: $status,
            externalReference: $payment->external_reference,
            payload: $payload,
        );

        return $payment->fresh(['order', 'transactions']);
    }

    public function markAsPaid(Payment $payment): void
    {
        $gateway = $this->gatewayFactory->resolve($payment->provider);
        $gateway->capture($payment);

        $payment->refresh();

        $this->logTransaction(
            payment: $payment,
            type: 'capture',
            amount: $payment->amount,
            status: PaymentStatus::PAID,
            externalReference: $payment->external_reference,
        );
    }

    public function markAsCancelled(Payment $payment): void
    {
        $gateway = $this->gatewayFactory->resolve($payment->provider);
        $gateway->cancel($payment);

        $payment->refresh();

        $this->logTransaction(
            payment: $payment,
            type: 'cancellation',
            amount: $payment->amount,
            status: PaymentStatus::CANCELLED,
            externalReference: $payment->external_reference,
        );
    }

    public function markAsRefunded(Payment $payment, float $amount): void
    {
        $gateway = $this->gatewayFactory->resolve($payment->provider);
        $gateway->refund($payment, $amount);

        $payment->refresh();

        $this->logTransaction(
            payment: $payment,
            type: 'refund',
            amount: $amount,
            status: PaymentStatus::REFUNDED,
            externalReference: $payment->external_reference,
        );
    }

    private function logTransaction(
        Payment $payment,
        string $type,
        float $amount,
        PaymentStatus $status,
        ?string $externalReference = null,
        ?array $payload = null,
    ): void {
        $transaction = new Transaction([
            'public_id' => (string) Str::uuid(),
            'transaction_type' => $type,
            'amount' => $amount,
            'currency' => $payment->currency,
            'status' => $status->value,
            'external_reference' => $externalReference,
            'raw_payload' => $payload,
        ]);

        $transaction->payment()->associate($payment);
        $transaction->save();
    }
}

