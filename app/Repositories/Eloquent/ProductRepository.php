<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()
            ->with(['category', 'brand', 'images' => function ($q): void {
                $q->orderBy('position');
            }])
            ->where('is_active', true);

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q): void {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('sku', 'like', '%' . $q . '%');
            });
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        if (! empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    public function featured(int $limit = 8): Collection
    {
        return $this->query()
            ->with(['images' => function ($q): void {
                $q->orderByDesc('is_primary')->orderBy('position');
            }])
            ->where('is_active', true)
            ->orderByDesc('promotional_price')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}

