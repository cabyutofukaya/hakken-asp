<?php

namespace App\Repositories\ReserveBase;

use App\Models\ReserveBase;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveBaseRepositoryInterface
{
    public function findForAgencyId(?int $id, int $agencyId, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve;
}
