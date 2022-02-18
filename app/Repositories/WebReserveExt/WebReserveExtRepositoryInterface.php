<?php

namespace App\Repositories\WebReserveExt;

use App\Models\WebReserveExt;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WebReserveExtRepositoryInterface
{
  public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebReserveExt;

  public function updateFields(int $id, array $params) : bool;

  public function updateWhere(array $where, array $param) : int;
}
