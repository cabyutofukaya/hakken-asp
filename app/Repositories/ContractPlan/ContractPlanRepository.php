<?php
namespace App\Repositories\ContractPlan;

use App\Models\ContractPlan;
use Illuminate\Support\Collection;

class ContractPlanRepository implements ContractPlanRepositoryInterface
{
    /**
    * @param ContractPlan $contractPlan
    */
    public function __construct(ContractPlan $contractPlan)
    {
        $this->contractPlan = $contractPlan;
    }

    public function all(string $order='id', string $sort='asc') : Collection
    {
        return $this->contractPlan->orderBy($order, $sort)->get();
    }
}
