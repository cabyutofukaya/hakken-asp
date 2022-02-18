<?php

namespace App\Repositories\VReserveInvoice;

use App\Models\VReserveInvoice;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface VReserveInvoiceRepositoryInterface
{
  public function findByReserveBundleInvoiceId(int $reserveBundleInvoiceId, array $with = [], array $select=[]) : ?VReserveInvoice;

  public function findByReserveInvoiceId(int $reserveInvoiceId, array $with = [], array $select=[]) : ?VReserveInvoice;

  public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;
}
