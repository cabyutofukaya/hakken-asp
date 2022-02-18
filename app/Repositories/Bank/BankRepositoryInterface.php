<?php

namespace App\Repositories\Bank;

use App\Models\Bank;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BankRepositoryInterface
{
    public function getWhere(array $where, array $select=[]): Collection;
    public function insert(array $rows) : void;
    public function truncate() : void;
}
