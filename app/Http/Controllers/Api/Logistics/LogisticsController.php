<?php

namespace App\Http\Controllers\Api\Logistics;

use App\Domain\Logistics\DataTransferObjects\FreightQuoteRequestData;
use App\Domain\Logistics\Services\FreightQuoteService;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogisticsController extends ApiController
{
    public function __construct(
        private readonly FreightQuoteService $freightQuoteService,
    ) {
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'zipcode' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight_kg' => ['nullable', 'numeric', 'min:0'],
            'items.*.volume_m3' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalQuantity = 0;
        $totalWeight = 0.0;
        $totalVolume = 0.0;

        foreach ($data['items'] as $item) {
            $qty = (int) $item['quantity'];
            $totalQuantity += $qty;
            $totalWeight += ($item['weight_kg'] ?? 0) * $qty;
            $totalVolume += ($item['volume_m3'] ?? 0) * $qty;
        }

        // Fallbacks similar to CheckoutService when detailed data is not provided.
        if ($totalWeight <= 0) {
            $totalWeight = max(1, $totalQuantity);
        }

        if ($totalVolume <= 0) {
            $totalVolume = 0.01 * max(1, $totalQuantity);
        }

        $requestDto = new FreightQuoteRequestData(
            originPostalCode: config('app.origin_postal_code', '01000-000'),
            destinationPostalCode: $data['zipcode'],
            weightKg: $totalWeight,
            volumeM3: $totalVolume,
            declaredValue: (float) ($request->input('declared_value', 0)),
        );

        $quote = $this->freightQuoteService->quote($requestDto);

        return $this->success([
            'total' => $quote->totalCost,
            'currency' => $quote->currency,
            'estimated_delivery_date' => $quote->estimatedDeliveryDate->format(DATE_ATOM),
            'breakdown' => $quote->breakdown,
        ]);
    }
}

