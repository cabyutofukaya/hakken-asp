<?php

namespace App\Repositories\ReserveBundleInvoice;

use App\Models\ReserveBundleInvoice;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveBundleInvoiceRepositoryInterface
{
  public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false) : ReserveBundleInvoice;
  
  public function isExistInvoice(int $businessUserId, string $cutoffDate) : bool;

  public function findWhere(array $where, array $with=[], array $select=[]) : ?ReserveBundleInvoice;
  
  public function create(array $data) : ReserveBundleInvoice;

  public function update(int $id, array $data): ReserveBundleInvoice;

  public function updateStatus(int $id, int $status) : bool;

  public function updateFields(int $id, array $params) : bool;

  public function deleteIfNoChild(int $id, bool $isSoftDelete = true) : void;
}
