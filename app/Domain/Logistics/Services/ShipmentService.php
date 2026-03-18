<?php

namespace App\Domain\Logistics\Services;

use App\Domain\Logistics\DataTransferObjects\ShipmentCreationData;
use App\Domain\Logistics\DataTransferObjects\ShipmentResultData;

class ShipmentService
{
    public function __construct(
        private readonly LogisticsService $logisticsService,
    ) {
    }

    public function createShipment(ShipmentCreationData $data): ShipmentResultData
    {
        return $this->logisticsService->createShipment($data);
    }
}

