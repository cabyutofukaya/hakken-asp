<?php

namespace App\Repositories\SupplierAccountPayable;

use App\Models\SupplierAccountPayable;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SupplierAccountPayableRepositoryInterface
{
  public function deleteBySupplierId(int $supplierId, bool $isSoftDelete) : bool;
}
