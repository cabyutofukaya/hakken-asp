<?php

namespace App\Repositories\ReserveConfirmBusinessUserManager;

use App\Models\ReserveConfirmBusinessUserManager;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveConfirmBusinessUserManagerRepositoryInterface
{
  public function create(array $data) : ReserveConfirmBusinessUserManager;
}
