<?php

namespace App\Repositories\DocumentRequestAll;

use App\Models\DocumentRequestAll;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DocumentRequestAllRepositoryInterface
{
    public function find(int $id, array $select = [], bool $getDeleted = false): DocumentRequestAll;

    public function findWhere(array $where, array $select = [], $getDeleted = false) : ?DocumentRequestAll;

    public function getWhere(array $where, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc"): Collection;

    public function paginateByAgencyId(int $agencyId, int $limit, array $with) : LengthAwarePaginator;

    public function create(array $data): DocumentRequestAll;

    public function maxSeq(int $agencyId);

    public function update(int $id, array $data): DocumentRequestAll;

    public function delete(int $id, bool $isSoftDelete): bool;
}
