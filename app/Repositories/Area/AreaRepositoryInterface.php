<?php

namespace App\Repositories\Area;

use App\Models\Area;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface AreaRepositoryInterface
{
    public function find(int $id, array $select): ?Area;

    public function findByUuid(string $uuid, array $select=[]) : ?Area;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function create(array $data): Area;

    public function update(int $id, array $data): Area;

    public function delete(int $id, bool $isSoftDelete): bool;

    public function getWhere(array $where, array $select = []) : Collection;
}
