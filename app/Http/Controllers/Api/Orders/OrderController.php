<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Api\ApiController;
use App\Domain\Logistics\Services\TrackingService;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    public function __construct(
        private readonly TrackingService $trackingService,
    ) {
    }

    public function index(Request $request)
    {
        $query = Order::query()->with(['items', 'payments', 'shipments']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate();

        return $this->success($orders);
    }

    public function show(string $publicId)
    {
        $order = Order::query()
            ->with(['items', 'payments', 'shipments'])
            ->where('public_id', $publicId)
            ->firstOrFail();

        return $this->success($order);
    }

    public function tracking(string $order)
    {
        $orderModel = Order::query()
            ->with('shipments.shippingMethod')
            ->where('public_id', $order)
            ->firstOrFail();

        $shipment = $orderModel->shipments->sortByDesc('created_at')->first();

        if (! $shipment || ! $shipment->tracking_code) {
            return $this->success([
                'tracking_code' => null,
                'status' => null,
                'history' => [],
                'carrier' => null,
            ]);
        }

        $details = $this->trackingService->getTrackingDetails($shipment->tracking_code);

        return $this->success([
            'tracking_code' => $details->trackingCode,
            'status' => $details->currentStatus,
            'history' => $details->history,
            'carrier' => $shipment->shippingMethod?->code ?? 'local',
        ]);
    }
}

