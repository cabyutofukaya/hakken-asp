<?php

namespace App\Repositories\DocumentCommon;

use App\Models\DocumentCommon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DocumentCommonRepositoryInterface
{
    public function find(int $id, array $select = [], bool $getDeleted = false): DocumentCommon;

    public function findWhere(array $where, array $select = []) : ?DocumentCommon;
    
    public function getWhere(array $where, array $select = [], bool $getDeleted = false): Collection;

    public function paginateByAgencyId(int $agencyId, int $limit, array $with) : LengthAwarePaginator;

    public function maxSeq(int $agencyId);

    public function create(array $data): DocumentCommon;

    public function update(int $id, array $data): DocumentCommon;

    public function delete(int $id, bool $isSoftDelete): bool;
}
