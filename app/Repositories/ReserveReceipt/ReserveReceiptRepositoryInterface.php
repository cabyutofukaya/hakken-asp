<?php

namespace App\Repositories\ReserveReceipt;

use App\Models\ReserveReceipt;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveReceiptRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveReceipt;

  public function findWhere(array $where, array $with = [], array $select = [], $getDeleted = false) : ?ReserveReceipt;

  public function updateOrCreate(array $attributes, array $values = []) : ReserveReceipt;

  public function create(array $data): ReserveReceipt;

  public function clearDocumentAddress(int $reserveId) : bool;
}
