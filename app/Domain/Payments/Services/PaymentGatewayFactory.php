<?php

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\LocalPaymentGateway;
use App\Infrastructure\Payments\Providers\AsaasGateway;
use App\Infrastructure\Payments\Providers\MercadoPagoGateway;
use App\Infrastructure\Payments\Providers\PagarmeGateway;
use App\Infrastructure\Payments\Providers\StripeGateway;
use Illuminate\Contracts\Container\Container;

class PaymentGatewayFactory
{
    /**
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    private array $providers = [
        'local' => LocalPaymentGateway::class,
        'mercadopago' => MercadoPagoGateway::class,
        'stripe' => StripeGateway::class,
        'pagarme' => PagarmeGateway::class,
        'asaas' => AsaasGateway::class,
    ];

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function resolve(?string $provider): PaymentGatewayInterface
    {
        $key = $provider !== null ? strtolower($provider) : 'local';

        $class = $this->providers[$key] ?? $this->providers['local'];

        /** @var PaymentGatewayInterface $gateway */
        $gateway = $this->container->make($class);

        return $gateway;
    }
}

