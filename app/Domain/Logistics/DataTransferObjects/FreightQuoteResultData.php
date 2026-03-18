<?php

namespace App\Domain\Logistics\DataTransferObjects;

class FreightQuoteResultData
{
    public function __construct(
        public readonly float $totalCost,
        public readonly string $currency,
        public readonly \DateTimeImmutable $estimatedDeliveryDate,
        public readonly array $breakdown = [],
    ) {
    }
}

