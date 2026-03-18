<?php

namespace App\Providers;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Logistics\Contracts\LogisticsGatewayInterface;
use App\Infrastructure\Payments\LocalPaymentGateway;
use App\Infrastructure\Logistics\LocalLogisticsGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerLogisticsBindings();
        $this->registerPaymentBindings();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    private function registerLogisticsBindings(): void
    {
        $this->app->bind(
            abstract: LogisticsGatewayInterface::class,
            concrete: LocalLogisticsGateway::class,
        );
    }

    private function registerPaymentBindings(): void
    {
        $this->app->bind(
            abstract: PaymentGatewayInterface::class,
            concrete: LocalPaymentGateway::class,
        );
    }
}

