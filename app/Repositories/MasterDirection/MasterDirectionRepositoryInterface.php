<?php

namespace App\Repositories\MasterDirection;

use App\Models\MasterDirection;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface MasterDirectionRepositoryInterface
{
    public function getIdByCode(string $code) : ?int;

    public function updateOrCreate(array $attributes, array $values = []) : Model;

    public function getWhere(array $where, array $with=[], array $select=[]) : Collection;

    public function deleteExceptionGenKey(string $genKey): bool;
}
