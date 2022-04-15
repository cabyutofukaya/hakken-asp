<?php

namespace App\Repositories\AspUser;

use App\Models\AspUser;
use Illuminate\Pagination\LengthAwarePaginator;

interface AspUserRepositoryInterface
{
    public function create(array $data) : AspUser;

    public function insert(array $rows) : bool;

    public function updateBulk(array $params, string $id) : bool;

    public function getWhereIds(array $where) : array;
}
