<?php

namespace App\Repositories\Contract;

use App\Models\Contract;

interface ContractRepositoryInterface
{
  public function find(int $id) : ?Contract;
  public function renewal() : int;
}
