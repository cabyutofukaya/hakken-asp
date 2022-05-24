<?php

namespace App\Repositories\AgencyWithdrawalItemHistory;

use Illuminate\Support\Collection;
use App\Models\AgencyWithdrawalItemHistory;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyWithdrawalItemHistoryRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = []): ?AgencyWithdrawalItemHistory;
  
  public function create(array $data): AgencyWithdrawalItemHistory;

  public function getSumAmountByReserveId(int $reserveId, bool $isLock=false) : int;
  
  // public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int;

  public function getWhere(array $where, array $with=[], array $select=[]) : Collection;
  
  public function findWhere(array $where, array $with=[], array $select=[]) : ?AgencyWithdrawalItemHistory;
  
  public function isExistsParticipant(int $participantId, int $reserveId) : bool;

  public function delete(int $id, bool $isSoftDelete): bool;

  public function deleteWhere(array $where, bool $isSoftDelete): bool;
}