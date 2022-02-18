<?php

namespace App\Repositories\City;

use App\Models\City;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CityRepositoryInterface
{
    public function find(int $id, array $select = [], bool $getDeleted = false): ?City;

    public function allByAgencyId(string $agencyId, array $with, array $select, string $order='id', string $direction='asc') : Collection;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function create(array $data): City;

    public function update(int $id, array $data): City;

    public function delete(int $id, bool $isSoftDelete): bool;
}
