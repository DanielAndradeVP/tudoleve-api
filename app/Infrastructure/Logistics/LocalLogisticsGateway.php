<?php

namespace App\Infrastructure\Logistics;

use App\Domain\Logistics\Contracts\LogisticsGatewayInterface;
use App\Domain\Logistics\DataTransferObjects\FreightQuoteRequestData;
use App\Domain\Logistics\DataTransferObjects\FreightQuoteResultData;
use App\Domain\Logistics\DataTransferObjects\ShipmentCreationData;
use App\Domain\Logistics\DataTransferObjects\ShipmentResultData;
use App\Domain\Logistics\DataTransferObjects\TrackingDetailsData;

/**
 * Temporary local implementation of the logistics gateway.
 *
 * This class simulates logistics behavior while there is no
 * dedicated logistics repository. In the future it will be
 * replaced by an HTTP-based implementation that calls the
 * logistics service API.
 */
class LocalLogisticsGateway implements LogisticsGatewayInterface
{
    public function quoteShipping(FreightQuoteRequestData $request): FreightQuoteResultData
    {
        // Simple fake calculation for now, just to simulate behavior.
        $baseCost = 10.0;
        $weightFactor = $request->weightKg * 2.0;
        $volumeFactor = $request->volumeM3 * 5.0;

        $totalCost = $baseCost + $weightFactor + $volumeFactor;

        return new FreightQuoteResultData(
            totalCost: $totalCost,
            currency: 'BRL',
            estimatedDeliveryDate: new \DateTimeImmutable('+5 days'),
            breakdown: [
                'base' => $baseCost,
                'weight' => $weightFactor,
                'volume' => $volumeFactor,
            ],
        );
    }

    public function createShipment(ShipmentCreationData $data): ShipmentResultData
    {
        // Simulate the generation of a tracking code and public shipment id.
        $trackingCode = 'TRK-' . strtoupper(bin2hex(random_bytes(4)));
        $shipmentPublicId = 'shp_' . bin2hex(random_bytes(8));

        return new ShipmentResultData(
            shipmentPublicId: $shipmentPublicId,
            trackingCode: $trackingCode,
            labelUrl: null,
        );
    }

    public function getTrackingDetails(string $trackingCode): TrackingDetailsData
    {
        // Simulated tracking information.
        return new TrackingDetailsData(
            trackingCode: $trackingCode,
            currentStatus: 'in_transit',
            deliveredAt: null,
            history: [
                [
                    'status' => 'created',
                    'occurred_at' => (new \DateTimeImmutable('-1 day'))->format(DATE_ATOM),
                ],
                [
                    'status' => 'in_transit',
                    'occurred_at' => (new \DateTimeImmutable('-12 hours'))->format(DATE_ATOM),
                ],
            ],
        );
    }

    public function updateShipmentStatus(string $trackingCode, string $status): void
    {
        // No-op in the local implementation; in a real logistics system
        // this would persist the new status and possibly trigger events.
    }

    public function generateShipmentLabel(string $trackingCode): string
    {
        // Return a placeholder URL that the frontend can treat as a label link.
        return sprintf('https://example.test/labels/%s.pdf', urlencode($trackingCode));
    }
}

