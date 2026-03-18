<?php

namespace App\Http\Controllers\Api\Payments;

use App\Domain\Payments\Services\PaymentService;
use App\Http\Controllers\Api\ApiController;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentAdminController extends ApiController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function capture(string $id, Request $request)
    {
        $payment = Payment::query()
            ->where('public_id', $id)
            ->firstOrFail();

        $this->paymentService->markAsPaid($payment);

        return $this->success([
            'payment' => $payment->fresh(['order', 'transactions']),
        ]);
    }

    public function cancel(string $id, Request $request)
    {
        $payment = Payment::query()
            ->where('public_id', $id)
            ->firstOrFail();

        $this->paymentService->markAsCancelled($payment);

        return $this->success([
            'payment' => $payment->fresh(['order', 'transactions']),
        ]);
    }

    public function refund(string $id, Request $request)
    {
        $payment = Payment::query()
            ->where('public_id', $id)
            ->firstOrFail();

        $amount = (float) ($request->input('amount', $payment->amount));

        $this->paymentService->markAsRefunded($payment, $amount);

        return $this->success([
            'payment' => $payment->fresh(['order', 'transactions']),
        ]);
    }
}

