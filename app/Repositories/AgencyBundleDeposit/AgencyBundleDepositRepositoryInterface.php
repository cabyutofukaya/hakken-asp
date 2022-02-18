<?php

namespace App\Repositories\AgencyBundleDeposit;

use Illuminate\Support\Collection;
use App\Models\AgencyBundleDeposit;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyBundleDepositRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false): AgencyBundleDeposit;
  
  public function create(array $data): AgencyBundleDeposit;

  // public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int;

  public function delete(int $id, bool $isSoftDelete = true): bool;

  public function deleteByIdentifierId(string $identifierId, bool $isSoftDelete = true): bool;
}