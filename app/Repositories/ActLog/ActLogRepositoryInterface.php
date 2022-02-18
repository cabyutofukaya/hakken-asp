<?php

namespace App\Repositories\ActLog;

use App\Models\ActLog;
use Illuminate\Pagination\LengthAwarePaginator;

interface ActLogRepositoryInterface
{
    public function paginate(int $limit, ?array $conditions, ?string $andOr, ?string $order, ?string $orderType): LengthAwarePaginator;

    public function create(array $data) : void;
}
