<?php

namespace App\Repositories\AccountPayableDetail;

use Illuminate\Support\Collection;
use App\Models\AccountPayableDetail;
use Illuminate\Pagination\LengthAwarePaginator;

interface AccountPayableDetailRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = [], bool $isLock = false): AccountPayableDetail;

  public function findWhere(array $where, array $with=[], array $select=[]) : ?AccountPayableDetail;

  public function whereExists($where) : ?AccountPayableDetail;

  public function save($data) : AccountPayableDetail;

  public function update(int $id, array $data): AccountPayableDetail;

  public function updateField(int $id, array $data): AccountPayableDetail;

  public function paginateByAgencyId(int $agencyId, array $params, int $limit, bool $isValid = true, ?string $applicationStep, array $with, array $select) : LengthAwarePaginator;

  // public function getByReserveNumber(string $reserveNumber, int $agencyId, ?string $applicationStep = null, array $with = [], array $select=[]) : Collection;

  public function updateOrCreate(array $where, array $params) : AccountPayableDetail;

  public function delete(int $id, bool $isSoftDelete): bool;
}