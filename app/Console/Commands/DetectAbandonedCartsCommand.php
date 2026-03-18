<?php

namespace App\Console\Commands;

use App\Domain\Cart\Services\AbandonedCartService;
use Illuminate\Console\Command;

class DetectAbandonedCartsCommand extends Command
{
    protected $signature = 'carts:detect-abandoned';

    protected $description = 'Detect abandoned carts and enqueue recovery notifications';

    public function handle(AbandonedCartService $abandonedCartService): int
    {
        $abandonedCartService->detectAbandonedCarts();

        $this->info('Abandoned carts detection executed.');

        return self::SUCCESS;
    }
}

