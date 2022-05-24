<?php

namespace App\Repositories\AgencyWithdrawal;

use Illuminate\Support\Collection;
use App\Models\AgencyWithdrawal;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyWithdrawalRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = []): AgencyWithdrawal;
  
  public function getIdsByTempId(string $tempID) : array;
  
  public function create(array $data): AgencyWithdrawal;

  public function insert(array $rows) : bool;
  
  public function getSumAmountByReserveId(int $reserveId, bool $isLock=false) : int;
  
  public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int;

  public function getWhere(array $where, array $with=[], array $select=[]) : Collection;
  
  public function isExistsParticipant(int $participantId, int $reserveId) : bool;

  public function delete(int $id, bool $isSoftDelete): bool;

  public function deleteWhere(array $where, bool $isSoftDelete) : bool;

  public function restore(int $id);
}