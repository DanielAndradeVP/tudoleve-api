<?php

namespace App\Jobs;

use App\Domain\Cart\Services\AbandonedCartService;
use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecoverAbandonedCartJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $cartId,
    ) {
    }

    public function handle(AbandonedCartService $abandonedCartService): void
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()->find($this->cartId);

        if (! $cart || $cart->abandoned_at === null) {
            return;
        }

        $abandonedCartService->sendRecoveryNotification($cart);
    }
}

