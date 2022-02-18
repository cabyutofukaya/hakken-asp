<?php

namespace App\Repositories\VDirection;

use App\Models\VDirection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface VDirectionRepositoryInterface
{
    public function findByUuid(string $uuid, array $select=[]) : ?VDirection;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $select) : LengthAwarePaginator;

    public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null) : Collection;
}
