<?php

namespace App\Repositories\UserCustomItem;

use App\Models\UserCustomItem;
use Illuminate\Support\Collection;

interface UserCustomItemRepositoryInterface
{
    public function find(int $id): UserCustomItem;

    public function findWhere(array $where, array $select = []) : ?UserCustomItem;

    public function getKeyByCodeForAgency(string $code, int $agencyId) : ?string;

    public function getWhere(array $where, array $select = null) : Collection;

    public function create(array $data) : UserCustomItem;

    public function update(string $id, $params): UserCustomItem;
    
    public function maxSeqForAgency(int $agencyId, $category) : int;

    public function getCategoriesForAgency(int $agencyId, $category) : Collection;

    public function getByKeys(array $keys, array $with = [], array $select = []) : Collection;

    public function getByCategoryCodeForAgencyId(string $code, int $agencyId, ?bool $flg, array $with = [], array $select = [], array $where = []) : Collection;

    public function delete(int $id, bool $isSoftDelete): bool;

    public function getByCodesForAgency(array $codes, int $agencyId, array $with = [], array $select = []) : Collection;
}

