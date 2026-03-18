<?php

namespace App\Domain\Payments\Contracts;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initializes a payment with the external provider.
     *
     * The returned array should contain provider-agnostic metadata that
     * the frontend can use to complete the payment (e.g., PIX QR code,
     * boleto URL, client_secret for credit card, etc.).
     *
     * @return array{
     *     provider: string,
     *     method: string,
     *     external_reference: string|null,
     *     metadata: array<mixed>
     * }
     */
    public function createPayment(Payment $payment, array $options = []): array;

    /**
     * Handles a webhook notification from the provider and updates the
     * corresponding Payment and Order records.
     *
     * The implementation is responsible for locating the Payment using
     * information from the payload (typically an external reference).
     */
    public function handleWebhook(array $payload): ?Payment;

    /**
     * Captures a previously authorized payment.
     */
    public function capture(Payment $payment, ?float $amount = null): void;

    /**
     * Cancels a payment that has not yet been captured/paid.
     */
    public function cancel(Payment $payment): void;

    /**
     * Issues a refund for a previously captured payment.
     */
    public function refund(Payment $payment, float $amount): void;
}

