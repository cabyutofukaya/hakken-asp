<?php

namespace App\Repositories\DocumentRequest;

use App\Models\DocumentRequest;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DocumentRequestRepositoryInterface
{
    public function find(int $id, array $select = [], bool $getDeleted = false): DocumentRequest;

    public function findWhere(array $where, array $select = [], $getDeleted = false) : ?DocumentRequest;
    
    public function getWhere(array $where, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc"): Collection;

    public function paginateByAgencyId(int $agencyId, int $limit, array $with) : LengthAwarePaginator;

    public function maxSeq(int $agencyId);

    public function create(array $data): DocumentRequest;

    public function update(int $id, array $data): DocumentRequest;

    public function delete(int $id, bool $isSoftDelete): bool;
}
