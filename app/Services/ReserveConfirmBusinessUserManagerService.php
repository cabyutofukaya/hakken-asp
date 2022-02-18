<?php

namespace App\Services;

use App\Models\ReserveConfirmBusinessUserManager;
use Illuminate\Support\Collection;
use App\Repositories\ReserveConfirmBusinessUserManager\ReserveConfirmBusinessUserManagerRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ReserveConfirmBusinessUserManagerService
{
    public function __construct(ReserveConfirmBusinessUserManagerRepository $reserveConfirmBusinessUserRepository)
    {
        $this->reserveConfirmBusinessUserRepository = $reserveConfirmBusinessUserRepository;
    }

    public function create(array $data): ReserveConfirmBusinessUserManager
    {
        return $this->reserveConfirmBusinessUserRepository->create($data);
    }

}
