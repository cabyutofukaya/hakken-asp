<?php

namespace App\Repositories\Direction;

use App\Models\Direction;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DirectionRepositoryInterface
{
    public function find(int $id, array $select): ?Direction;

    public function findByUuid(string $uuid, array $select=[]) : ?Direction;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $select) : LengthAwarePaginator;

    public function create(array $data): Direction;

    public function update(int $id, array $data): Direction;

    public function delete(int $id, bool $isSoftDelete): bool;

    public function getWhere(array $where, array $select = []) : Collection;
}
