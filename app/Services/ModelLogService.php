<?php

namespace App\Services;

use App\Repositories\ModelLog\ModelLogRepository;

class ModelLogService
{
    private $modelLogRepository;

    public function __construct(ModelLogRepository $modelLogRepository)
    {
        $this->modelLogRepository = $modelLogRepository;
    }

    public function paginate($limit, $conditions, $andOr=null, $order="id", $orderType="desc")
    {
        $conditions = array_filter($conditions, 'strlen');
        return $this->modelLogRepository->paginate($limit, $conditions, $andOr, $order, $orderType);
    }

    public function find(int $id)
    {
        return $this->modelLogRepository->find($id);
    }

}
