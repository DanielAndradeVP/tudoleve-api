<?php

namespace App\Jobs;

use App\Domain\Payments\Services\PaymentService;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetryPaymentConfirmationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $paymentId,
        private readonly array $lastPayload = [],
    ) {
        $this->onQueue('payments');

        // 3 tentativas no total
        $this->tries = 3;
    }

    public function backoff(): array
    {
        // 1 min, 5 min, 15 min
        return [60, 300, 900];
    }

    public function handle(PaymentService $paymentService): void
    {
        $payment = Payment::query()->find($this->paymentId);

        if (! $payment) {
            return;
        }

        // In a real implementation this would query the provider's API
        // for the latest status. For now we just re-process the last
        // known payload if available.
        if ($this->lastPayload !== []) {
            $paymentService->handleWebhook($payment->provider ?? 'local', $this->lastPayload);
        }
    }
}

