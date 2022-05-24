<?php

namespace App\Repositories\AccountPayableItem;

use Illuminate\Support\Collection;
use App\Models\AccountPayableItem;
use Illuminate\Pagination\LengthAwarePaginator;

interface AccountPayableItemRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = [], bool $isLock = false): ?AccountPayableItem;

  public function findWhere(array $where, array $with=[], array $select=[]) : ?AccountPayableItem;

  public function update(int $id, array $data): AccountPayableItem;

  public function updateField(int $id, array $data): AccountPayableItem;
  
  public function refreshAmountByReserveItineraryId(int $reserveItineraryId) : bool;public function paginateByAgencyId(int $agencyId, array $params, int $limit, ?string $applicationStep, array $with, array $select, bool $exZero = true) : LengthAwarePaginator;

  public function deleteExceptSupplierIdsForReserveItineraryId(int $reserveItineraryId, array $supplierIds, bool $isSoftDelete = true) : bool;
}