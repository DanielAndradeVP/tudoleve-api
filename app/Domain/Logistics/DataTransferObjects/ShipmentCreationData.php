<?php

namespace App\Domain\Logistics\DataTransferObjects;

class ShipmentCreationData
{
    public function __construct(
        public readonly string $orderPublicId,
        public readonly string $recipientName,
        public readonly string $recipientPostalCode,
        public readonly string $recipientAddress,
        public readonly float $weightKg,
        public readonly float $volumeM3,
    ) {
    }
}

