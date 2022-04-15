<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * ページネーションで一覧を取得
     *
     * @var int $limit
     * @return object
     */
    public function paginate(int $limit, array $with): LengthAwarePaginator;

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false) : User;

    public function getIdByUserNumber(string $userNumber, int $agencyId) : ?int;

    public function findWhere(array $where, array $with=[], array $select=[]) : ?User;
    
    public function applicantSearch(int $agencyId, ?string $name, ?string $userNumber, array $with = [], array $select = [], ?int $limit = null, bool $getDeleted = false) : Collection;

    public function getWhere(array $where, array $with=[], array $select=[], $limit=null) : Collection;

    public function getIdInfoByUserableId(int $agencyId, string $userableType, array $userableIds) : array;

    public function create(array $data) : User;

    public function update(int $id, array $data): User;

    public function insert(array $rows) : bool;

    public function updateField(int $userId, array $params) : bool;

    public function delete(int $id, bool $isSoftDelete): bool;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function findByUserNumberForAgencyId($userNumber, $agencyId): ?User;

    public function findByWebUserId(int $webUserId, int $agencyId, bool $getDeleted = false) : ?User;
}
