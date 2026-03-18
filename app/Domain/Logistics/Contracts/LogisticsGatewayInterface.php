<?php

namespace App\Domain\Logistics\Contracts;

use App\Domain\Logistics\DataTransferObjects\FreightQuoteRequestData;
use App\Domain\Logistics\DataTransferObjects\FreightQuoteResultData;
use App\Domain\Logistics\DataTransferObjects\ShipmentCreationData;
use App\Domain\Logistics\DataTransferObjects\ShipmentResultData;
use App\Domain\Logistics\DataTransferObjects\TrackingDetailsData;

/**
 * Abstraction over any logistics provider.
 *
 * The main backend must depend only on this interface and
 * business-level operations, never on concrete carrier details.
 */
interface LogisticsGatewayInterface
{
    public function quoteShipping(FreightQuoteRequestData $request): FreightQuoteResultData;

    public function createShipment(ShipmentCreationData $data): ShipmentResultData;

    public function getTrackingDetails(string $trackingCode): TrackingDetailsData;

    public function updateShipmentStatus(string $trackingCode, string $status): void;

    public function generateShipmentLabel(string $trackingCode): string;
}

