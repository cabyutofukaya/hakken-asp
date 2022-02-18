<?php

namespace App\Repositories\Staff;

use App\Models\Staff;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


interface StaffRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return LengthAwarePaginator
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function find(int $id, array $with, array $select, bool $getDeleted) : ?Staff;

    public function findWhere(array $where, array $select) : ?Staff;

    public function create(array $data) : Staff;

    public function update(int $id, array $data) : Staff;

    public function updateFields(int $staffId, array $params): bool;

    public function delete(int $id) : int;

    public function countByAgencyId(int $agencyId) : int;

    public function getCountByAgencyRoleId(int $agencyRoleId): int;

    public function getWhere(array $where, array $select = [], bool $getDeleted = false): Collection;

    public function updateWhere(array $where, array $param) : int;
}
