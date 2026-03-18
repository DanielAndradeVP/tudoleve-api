<?php

namespace App\Repositories\Eloquent;

use App\Models\ShippingMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShippingMethodRepository extends BaseRepository
{
    public function __construct(ShippingMethod $model)
    {
        parent::__construct($model);
    }

    /**
     * @return iterable<ShippingMethod>
     */
    public function listActive(array $filters = [], ?int $perPage = null): iterable
    {
        $query = $this->query()->where('is_active', true);

        if ($perPage !== null) {
            /** @var LengthAwarePaginator $result */
            $result = $query->paginate($perPage);

            return $result;
        }

        return $query->orderBy('name')->get();
    }
}

