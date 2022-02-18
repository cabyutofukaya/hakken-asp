<?php

namespace App\Repositories\ContractPlan;

use App\Models\ContractPlan;
use Illuminate\Support\Collection;

interface ContractPlanRepositoryInterface
{
  public function all(string $order, string $sort) : Collection;

}
