<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Domain\Catalog\Services\BrandCatalogService;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class BrandController extends ApiController
{
    public function __construct(
        private readonly BrandCatalogService $catalogService,
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'q' => $request->query('q'),
        ];

        $perPage = (int) $request->query('per_page', 50);

        $brands = $this->catalogService->listBrands($filters, $perPage);

        return $this->success($brands);
    }
}

