<?php

namespace App\Domain\Cart\Services;

use App\Jobs\RecoverAbandonedCartJob;
use App\Models\Cart;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AbandonedCartService
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {
    }

    public function detectAbandonedCarts(): void
    {
        $threshold = Carbon::now()->subHours(24);

        Cart::query()
            ->whereNull('abandoned_at')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($threshold): void {
                $query->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '<=', $threshold);
            })
            ->whereHas('items')
            ->chunkById(100, function ($carts): void {
                /** @var Cart $cart */
                foreach ($carts as $cart) {
                    $this->markCartAsAbandoned($cart);
                    RecoverAbandonedCartJob::dispatch($cart->id);
                }
            });
    }

    public function markCartAsAbandoned(Cart $cart): void
    {
        $cart->abandoned_at = Carbon::now();

        if (! $cart->recovery_token) {
            $cart->recovery_token = $this->generateRecoveryToken($cart);
        }

        $cart->save();
    }

    public function generateRecoveryToken(Cart $cart): string
    {
        return Str::uuid()->toString();
    }

    public function sendRecoveryNotification(Cart $cart): void
    {
        $recoveryUrl = url('/cart/recover/' . $cart->recovery_token);

        Log::info('Abandoned cart recovery notification', [
            'cart_id' => $cart->id,
            'customer_id' => $cart->customer_id,
            'recovery_url' => $recoveryUrl,
        ]);

        // Here you could integrate with email or WhatsApp providers.
    }
}

