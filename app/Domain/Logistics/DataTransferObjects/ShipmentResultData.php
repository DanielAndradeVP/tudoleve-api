<?php

namespace App\Domain\Logistics\DataTransferObjects;

class ShipmentResultData
{
    public function __construct(
        public readonly string $shipmentPublicId,
        public readonly string $trackingCode,
        public readonly ?string $labelUrl = null,
    ) {
    }
}

