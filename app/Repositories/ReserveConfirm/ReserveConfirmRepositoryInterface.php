<?php

namespace App\Repositories\ReserveConfirm;

use App\Models\ReserveConfirm;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveConfirmRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveConfirm;
  public function findWhere(array $where, array $with=[], array $select=[]) : ?ReserveConfirm;
  public function create(array $data) : ReserveConfirm;
  public function updateStatus(int $id, int $status) : bool;
  public function update(int $id, array $data): ReserveConfirm;
  public function clearDocumentAddress(int $reserveId) : bool;
  public function updateFields(int $id, array $params) : ReserveConfirm;
  public function getByReserveItineraryId(int $reserveItineraryId, array $with=[], array $select=[]) : Collection;
  public function findByDocumentQuoteCodeByReserveItineraryId(int $reserveItineraryId, string $code, array $with =[], array $select = [], bool $getDeleted = false) : ?ReserveConfirm;
  public function getCountByReserveItineraryId(int $reserveItineraryId, bool $includeDeleted = true) : int;
  public function delete(int $id, bool $isSoftDelete): bool;
}
