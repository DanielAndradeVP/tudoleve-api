<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        if ($data instanceof LengthAwarePaginator) {
            $meta = array_merge($meta, [
                'current_page' => $data->currentPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ]);

            $data = $data->items();
        }

        if ($data instanceof JsonResource) {
            $data = $data->response()->getData(true);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => (object) $meta,
        ], $status);
    }

    protected function error(
        string $message,
        int $status = 400,
        array $errors = [],
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
            'meta' => (object) $meta,
        ], $status);
    }
}

