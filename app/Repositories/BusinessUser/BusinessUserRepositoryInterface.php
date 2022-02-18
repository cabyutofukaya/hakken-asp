<?php

namespace App\Repositories\BusinessUser;

use App\Models\BusinessUser;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BusinessUserRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return object
     */
    public function paginate(int $limit, array $with): LengthAwarePaginator;

    public function find(int $id) : ?BusinessUser;

    public function getIdByUserNumber(string $userNumber, int $agencyId) : ?int;

    public function findWhere(array $where, array $with=[], array $select=[]) : ?BusinessUser;
    
    public function getWhere(array $where, array $with=[], array $select=[], $limit=null) : Collection;

    public function create(array $data) : BusinessUser;

    public function update(int $id, array $data): BusinessUser;

    public function updateField(int $userId, array $params) : bool;

    public function delete(int $id, bool $isSoftDelete): bool;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function findByUserNumberForAgencyId($userNumber, $agencyId): ?BusinessUser;
}
