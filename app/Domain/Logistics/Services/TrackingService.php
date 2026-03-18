<?php

namespace App\Domain\Logistics\Services;

use App\Domain\Logistics\DataTransferObjects\TrackingDetailsData;

class TrackingService
{
    public function __construct(
        private readonly LogisticsService $logisticsService,
    ) {
    }

    public function getTrackingDetails(string $trackingCode): TrackingDetailsData
    {
        return $this->logisticsService->getTrackingDetails($trackingCode);
    }
}

