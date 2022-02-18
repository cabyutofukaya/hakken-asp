<?php

namespace App\Repositories\Interest;

use App\Models\Interest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InterestRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return object
     */
    public function paginate(int $limit, array $with) : LengthAwarePaginator;

    public function find(int $id) : Interest;

    public function all() : Collection;
    
    public function create(array $data) : Interest;

    public function update(int $id, array $data) : int;

    public function delete(int $id) : int;
}
