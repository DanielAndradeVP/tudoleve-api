<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Domain\Catalog\Services\CategoryCatalogService;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function __construct(
        private readonly CategoryCatalogService $catalogService,
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'q' => $request->query('q'),
        ];

        $perPage = (int) $request->query('per_page', 50);

        $categories = $this->catalogService->listCategories($filters, $perPage);

        return $this->success($categories);
    }
}

