<?php

namespace App\Services;

use App\Repositories\ContractPlan\ContractPlanRepository;
use Illuminate\Http\Request;

class ContractPlanService
{
    public function __construct(ContractPlanRepository $contractPlanRepository)
    {
        $this->contractPlanRepository = $contractPlanRepository;
    }

    /**
     * IDと名称の一覧を取得
     * 
     * @return array
     */
    public function getList() : array
    {
        return $this->contractPlanRepository->all()->pluck("name", "id")->toArray();
    }
}
