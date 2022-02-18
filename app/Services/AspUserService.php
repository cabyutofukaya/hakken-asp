<?php

namespace App\Services;

use App\Models\AspUser;
use App\Repositories\AspUser\AspUserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class AspUserService
{
    public function __construct(
        AspUserRepository $aspUserRepository
    ) {
        $this->aspUserRepository = $aspUserRepository;
    }

    public function create(array $data) : AspUser
    {
        return $this->aspUserRepository->create($data);
    }
}
