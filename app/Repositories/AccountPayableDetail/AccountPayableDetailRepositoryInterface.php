<?php

namespace App\Repositories\AccountPayableDetail;

use Illuminate\Support\Collection;
use App\Models\AccountPayableDetail;
use Illuminate\Pagination\LengthAwarePaginator;

interface AccountPayableDetailRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = [], bool $isLock = false): ?AccountPayableDetail;

  public function getWhere(array $where, array $with=[], array $select=[]) : Collection;
  
  public function getIdsBySaleableIds(string $saleableType, array $saleableIds) : array;
  
  public function findWhere(array $where, array $with=[], array $select=[]) : ?AccountPayableDetail;

  public function whereExists($where) : bool;

  public function getSummarizeItemQuery(int $agencyId, int $reserveId, int $reserveItineraryId, int $supplierId, string $subject, int $itemId);

  public function save($data) : AccountPayableDetail;

  public function update(int $id, array $data): AccountPayableDetail;

  public function updateField(int $id, array $data): AccountPayableDetail;

  public function updateWhere(array $update, array $where) : bool;

  public function updateWhereBulk(array $where, array $params, string $id='id') : bool;

  public function insert(array $params) : bool;

  public function updateBulk(array $params, string $id) : bool;
  
  public function paginateByAgencyId(int $agencyId, array $params, int $limit, ?string $applicationStep, array $with, array $select, bool $exZero = true) : LengthAwarePaginator;
  
  public function updateOrCreate(array $where, array $params) : AccountPayableDetail;

  public function delete(int $id, bool $isSoftDelete): bool;
}