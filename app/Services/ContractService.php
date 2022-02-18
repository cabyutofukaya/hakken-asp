<?php

namespace App\Services;

use \Route;
use App\Repositories\Contract\ContractRepository;
use Illuminate\Http\Request;

class ContractService
{
    public function __construct(ContractRepository $contractRepository)
    {
        $this->contractRepository = $contractRepository;
    }

    public function renewal() : int
    {
        return $this->contractRepository->renewal();
    }
}
