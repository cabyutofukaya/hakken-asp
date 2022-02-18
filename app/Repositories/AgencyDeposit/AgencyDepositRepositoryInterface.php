<?php

namespace App\Repositories\AgencyDeposit;

use Illuminate\Support\Collection;
use App\Models\AgencyDeposit;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyDepositRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false): AgencyDeposit;
  
  public function findWhere(array $where, array $with=[], array $select=[]) : ?AgencyDeposit;

  public function create(array $data): AgencyDeposit;

  // public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int;

  public function delete(int $id, bool $isSoftDelete = true): bool;

  public function deleteByIdentifierId(string $identifierId, bool $isSoftDelete = true): bool;
}