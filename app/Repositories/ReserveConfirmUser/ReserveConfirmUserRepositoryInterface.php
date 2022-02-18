<?php

namespace App\Repositories\ReserveConfirmUser;

use App\Models\ReserveConfirmUser;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveConfirmUserRepositoryInterface
{
  public function create(array $data) : ReserveConfirmUser;
}
