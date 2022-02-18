<?php

namespace App\Repositories\AccountPayable;

use Illuminate\Support\Collection;
use App\Models\AccountPayable;
use Illuminate\Pagination\LengthAwarePaginator;

interface AccountPayableRepositoryInterface
{
  public function updateOrCreate(array $where, array $params) : AccountPayable;
  public function getByReserveItineraryId(int $reserveItineraryId, array $with=[], array $select=[], bool $getDeleted = false) : Collection;
  public function whereExists($where) : ?AccountPayable;
  // public function deleteNoDetailByReserveItineraryId(int $reserveItineraryId, bool $isSoftDelete = true) : bool;
  public function save($data) : AccountPayable;
  public function deleteLostPurchaseData(int $reserveItineraryId, array $supplierIdIds, bool $isSoftDelete = true) : bool;
  public function deleteDoseNotHaveDetails(int $reserveId, bool $isSoftDelete = true) : bool;
}