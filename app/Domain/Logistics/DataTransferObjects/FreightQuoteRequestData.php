<?php

namespace App\Domain\Logistics\DataTransferObjects;

class FreightQuoteRequestData
{
    public function __construct(
        public readonly string $originPostalCode,
        public readonly string $destinationPostalCode,
        public readonly float $weightKg,
        public readonly float $volumeM3,
        public readonly ?float $declaredValue = null,
    ) {
    }
}

