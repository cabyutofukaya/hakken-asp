<?php

namespace App\Services;

use App\Models\ReserveConfirmUser;
use Illuminate\Support\Collection;
use App\Repositories\ReserveConfirmUser\ReserveConfirmUserRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ReserveConfirmUserService
{
    public function __construct(ReserveConfirmUserRepository $reserveConfirmUserRepository)
    {
        $this->reserveConfirmUserRepository = $reserveConfirmUserRepository;
    }

    public function create(array $data): ReserveConfirmUser
    {
        return $this->reserveConfirmUserRepository->create($data);
    }

}
