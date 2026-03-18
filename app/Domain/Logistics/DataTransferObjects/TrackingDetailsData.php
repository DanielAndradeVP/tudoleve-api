<?php

namespace App\Domain\Logistics\DataTransferObjects;

class TrackingDetailsData
{
    public function __construct(
        public readonly string $trackingCode,
        public readonly string $currentStatus,
        public readonly ?\DateTimeImmutable $deliveredAt = null,
        public readonly array $history = [],
    ) {
    }
}

