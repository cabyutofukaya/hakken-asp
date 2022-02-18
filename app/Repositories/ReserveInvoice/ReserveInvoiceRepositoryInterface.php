<?php

namespace App\Repositories\ReserveInvoice;

use App\Models\ReserveInvoice;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveInvoiceRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveInvoice;

  public function findByReserveId(int $reserveId, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveInvoice;
  
  public function paginateByReserveBundleInvoiceId(int $agencyId, int $reserveBundleInvoiceId, int $limit, array $with = [], array $select = [], bool $getDeleted = false) : LengthAwarePaginator;

  public function getWhere(array $where, array $with = [], array $select = [], bool $getDeleted = false): Collection;
  
  public function getWhereIn(string $column, array $vals, array $with = [], array $select = [], bool $getDeleted = false): Collection;

  public function updateOrCreate(array $attributes, array $values = []) : ReserveInvoice;

  public function updateFields(int $reserveInvoiceId, array $params) : bool;
  
  public function clearDocumentAddress(int $reserveId) : bool;
}
