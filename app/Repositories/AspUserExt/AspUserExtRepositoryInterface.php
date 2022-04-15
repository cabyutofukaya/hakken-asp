<?php

namespace App\Repositories\AspUserExt;

use App\Models\AspUserExt;

interface AspUserExtRepositoryInterface
{
    public function insert(array $rows) : bool;
}
