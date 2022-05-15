<?php

namespace App\Repositories\SupplierPaymentDate;

use App\Models\SupplierPaymentDate;

interface SupplierPaymentDateRepositoryInterface
{
  public function findWhere(array $where, array $with=[], array $select=[]) : ?SupplierPaymentDate;

  public function insert(array $rows) : bool;

  public function updateBulk(array $params, string $id) : bool;
}