<?php

namespace App\Repositories\AspUser;

use App\Models\AspUser;
use Illuminate\Pagination\LengthAwarePaginator;

interface AspUserRepositoryInterface
{
    public function create(array $data) : AspUser;
}
