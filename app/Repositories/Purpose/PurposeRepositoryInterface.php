<?php

namespace App\Repositories\Purpose;

use Illuminate\Support\Collection;

interface PurposeRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return object
     */
    public function paginate(int $limit, array $with);

    public function find(int $id);

    public function getNamesByIds(array $ids): array;

    public function all() : Collection;

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);
}
