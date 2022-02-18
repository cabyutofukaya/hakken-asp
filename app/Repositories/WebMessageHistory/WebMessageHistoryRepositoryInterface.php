<?php

namespace App\Repositories\WebMessageHistory;

use App\Models\WebMessageHistory;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WebMessageHistoryRepositoryInterface
{
  public function paginateByAgencyId(int $agencyId, array $params = [], int $limit, array $with = [], array $select =[]) : LengthAwarePaginator;

  public function updateOrCreate(array $attributes, array $values = []) : WebMessageHistory;

  public function getMessage(int $reserveId) : ?string;

  public function updateWhere(array $where, array $param) : int;

  public function isExistsByReserveId(int $reserveId) : bool;
}
