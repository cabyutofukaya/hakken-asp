<?php

namespace App\Repositories\Agency;

use App\Models\Agency;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return LengthAwarePaginator
     */
    public function paginate(array $params, int $limit, array $with) : LengthAwarePaginator;

    public function find(int $id) : ?Agency;

    public function findBy(array $conditions): ?Agency;

    public function isAccountExists(string $account) : bool;

    public function getIdByAccount(string $account) : int;

    public function selectSearchCompanyName(string $name, ?int $exclusionId, int $limit) : array;

    public function create(array $data) : Agency;

    public function update(int $id, array $data) : Agency;

    public function updateField(int $id, array $params) : bool;

    public function delete(int $id, bool $isSoftDelete): bool;
}
