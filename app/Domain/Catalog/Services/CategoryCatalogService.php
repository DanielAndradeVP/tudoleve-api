<?php

namespace App\Domain\Catalog\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryCatalogService
{
    public function listCategories(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = Category::query();

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

