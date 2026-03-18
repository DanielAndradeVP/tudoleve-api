<?php

namespace App\Console;

use App\Console\Commands\DetectAbandonedCartsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        DetectAbandonedCartsCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('carts:detect-abandoned')->hourly();
    }
}

