<?php

namespace App\Repositories\AccountPayableReserve;

use Illuminate\Support\Collection;
use App\Models\AccountPayableReserve;
use Illuminate\Pagination\LengthAwarePaginator;

interface AccountPayableReserveRepositoryInterface
{
  public function findByReserveId(int $reserveId, array $with = [], array $select=[], bool $isLock = false) : ?AccountPayableReserve;
  
  public function updateOrCreate(array $where, array $params) : AccountPayableReserve;

  public function updateField(int $id, array $data): AccountPayableReserve;

  public function refreshAmountByReserveId(int $reserveId, ?int $reserveItineraryId) : bool;
}