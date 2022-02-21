<?php

namespace App\Repositories\ReserveDeparted;

use App\Models\Reserve;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveDepartedRepositoryInterface
{
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : Reserve;
    
    public function findByControlNumber(string $controlNumber, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : ?Reserve;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function delete(int $id, bool $isSoftDelete): bool;
}
