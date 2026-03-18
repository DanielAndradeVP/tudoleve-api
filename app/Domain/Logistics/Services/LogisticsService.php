<?php

namespace App\Domain\Logistics\Services;

use App\Domain\Logistics\Contracts\LogisticsGatewayInterface;
use App\Domain\Logistics\DataTransferObjects\FreightQuoteRequestData;
use App\Domain\Logistics\DataTransferObjects\FreightQuoteResultData;
use App\Domain\Logistics\DataTransferObjects\ShipmentCreationData;
use App\Domain\Logistics\DataTransferObjects\ShipmentResultData;
use App\Domain\Logistics\DataTransferObjects\TrackingDetailsData;

class LogisticsService
{
    public function __construct(
        private readonly LogisticsGatewayInterface $gateway,
    ) {
    }

    public function quoteShipping(FreightQuoteRequestData $request): FreightQuoteResultData
    {
        return $this->gateway->quoteShipping($request);
    }

    public function createShipment(ShipmentCreationData $data): ShipmentResultData
    {
        return $this->gateway->createShipment($data);
    }

    public function getTrackingDetails(string $trackingCode): TrackingDetailsData
    {
        return $this->gateway->getTrackingDetails($trackingCode);
    }

    public function updateShipmentStatus(string $trackingCode, string $status): void
    {
        $this->gateway->updateShipmentStatus($trackingCode, $status);
    }

    public function generateShipmentLabel(string $trackingCode): string
    {
        return $this->gateway->generateShipmentLabel($trackingCode);
    }
}

