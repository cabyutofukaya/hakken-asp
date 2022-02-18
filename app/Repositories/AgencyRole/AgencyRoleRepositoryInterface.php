<?php

namespace App\Repositories\AgencyRole;

use App\Models\AgencyRole;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyRoleRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): AgencyRole;
    public function find(int $id): AgencyRole;
    public function update(int $id, array $data): AgencyRole;
    public function delete(int $id): int;
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, array $with, bool $getStaffCount): LengthAwarePaginator;
    public function getMasterRoleId(int $agencyId): int;
    public function getDefaultRoles(int $agencyId): array;
    public function getWhere(array $where, array $select): Collection;
}
