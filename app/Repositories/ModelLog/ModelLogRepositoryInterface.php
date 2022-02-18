<?php

namespace App\Repositories\ModelLog;

use App\Models\ModelLog;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModelLogRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return object
     */
    public function paginate(int $limit, ?array $conditions, ?string $andOr, ?string $order, ?string $orderType): LengthAwarePaginator;

    public function find(int $id): ?ModelLog;
}
