<?php

namespace App\Repositories\Inflow;

use App\Models\Inflow;
use Illuminate\Pagination\LengthAwarePaginator;

interface InflowRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return object
     */
    public function paginate(int $limit): LengthAwarePaginator;

    public function all():object;

    public function find(int $id):Inflow;

    public function create(array $data):Inflow;

    public function update(int $id, array $data): Inflow;

    public function delete(int $id): int;
}
