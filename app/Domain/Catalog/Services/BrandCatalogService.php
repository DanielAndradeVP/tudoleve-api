<?php

namespace App\Domain\Catalog\Services;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandCatalogService
{
    public function listBrands(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = Brand::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q): void {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        $query->orderBy('name');

        return $query->paginate($perPage);
    }
}

