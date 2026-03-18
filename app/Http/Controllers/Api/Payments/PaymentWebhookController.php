<?php

namespace App\Http\Controllers\Api\Payments;

use App\Domain\Payments\Services\PaymentService;
use App\Http\Controllers\Api\ApiController;
use App\Jobs\RetryPaymentConfirmationJob;
use Illuminate\Http\Request;

class PaymentWebhookController extends ApiController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function handle(Request $request, string $provider)
    {
        $allowedProviders = ['local', 'mercadopago', 'stripe', 'pagarme', 'asaas'];
        $normalizedProvider = strtolower($provider);

        if (! in_array($normalizedProvider, $allowedProviders, true)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        $payload = $request->all();

        try {
            $payment = $this->paymentService->handleWebhook($normalizedProvider, $payload);
        } catch (\Throwable $e) {
            if (isset($payload['payment_id'])) {
                RetryPaymentConfirmationJob::dispatch($payload['payment_id'], $payload)
                    ->delay(now()->addMinute());
            }

            return $this->error('Failed to process payment webhook.', 500);
        }

        if (! $payment) {
            return $this->error('Payment not found or payload invalid.', 404);
        }

        return $this->success([
            'payment' => $payment,
        ]);
    }
}

