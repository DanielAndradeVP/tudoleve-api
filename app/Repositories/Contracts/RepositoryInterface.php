<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function all(array $columns = ['*']): iterable;

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    public function find(int|string $id, array $columns = ['*']): ?Model;

    public function create(array $attributes): Model;

    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): void;
}

