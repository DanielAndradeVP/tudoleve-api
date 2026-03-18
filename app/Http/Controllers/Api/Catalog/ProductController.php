<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Domain\Catalog\Services\ProductCatalogService;
use App\Http\Controllers\Api\ApiController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function __construct(
        private readonly ProductCatalogService $catalogService,
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'q' => $request->query('q'),
            'category_id' => $request->query('category_id'),
            'brand_id' => $request->query('brand_id'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'sort' => $request->query('sort'),
        ];

        $perPage = (int) $request->query('per_page', 15);

        $products = $this->catalogService->listProducts($filters, $perPage);

        return $this->success($products);
    }

    public function show(string $publicId)
    {
        $product = $this->catalogService->getByPublicId($publicId);

        return $this->success($product);
    }

    public function featured()
    {
        $products = $this->catalogService->listFeatured(8);

        $data = $products->map(function (Product $product): array {
            $primaryImage = $product->images
                ->sortByDesc('is_primary')
                ->sortBy('position')
                ->first();

            return [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => (float) ($product->promotional_price ?? $product->price),
                'image' => $primaryImage?->url,
            ];
        })->values();

        return $this->success($data);
    }
}

