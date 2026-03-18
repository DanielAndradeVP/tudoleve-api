<?php

namespace App\Domain\Catalog\Services;

use App\Models\Product;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductCatalogService
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    public function listProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->products->search($filters, $perPage);
    }

    public function getByPublicId(string $publicId): Product
    {
        /** @var Product|null $product */
        $product = Product::query()
            ->with(['category', 'brand', 'images' => fn ($q) => $q->orderBy('position')])
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->firstOrFail();

        return $product;
    }

    public function listFeatured(int $limit = 8): Collection
    {
        return $this->products->featured($limit);
    }
}

