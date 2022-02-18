<?php

namespace App\Repositories\VArea;

use App\Models\VArea;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface VAreaRepositoryInterface
{
    public function find(int $id, array $select): ?VArea;

    public function findByUuid(string $uuid, array $select=[]) : ?VArea;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function getByAgencyAccount(int $agencyId, array $select = []) : Collection;
    
    public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null) : Collection;
}
