<?php

namespace App\Repositories\Role;

use App\Models\Role;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): Role;
    public function find(int $id): Role;
    public function update(int $id, array $data): Role;
    public function delete(int $id): int;
    public function getIdByNameEn(string $nameEn): ?int;
}
