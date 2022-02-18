<?php

namespace App\Repositories\Prefecture;

use Illuminate\Pagination\LengthAwarePaginator;

interface PrefectureRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return object
     */
    public function paginate(int $limit) : LengthAwarePaginator;

    public function all();
}
