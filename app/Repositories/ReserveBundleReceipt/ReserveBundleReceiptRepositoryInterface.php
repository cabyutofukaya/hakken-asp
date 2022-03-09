<?php

namespace App\Repositories\ReserveBundleReceipt;

use App\Models\ReserveBundleReceipt;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveBundleReceiptRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveBundleReceipt;

  public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?ReserveBundleReceipt;
  
  public function updateOrCreate(array $attributes, array $values = []) : ReserveBundleReceipt;

  public function updateStatus(int $id, int $status) : bool;
}
