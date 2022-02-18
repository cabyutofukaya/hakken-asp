<?php

namespace App\Repositories\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SupplierRepositoryInterface
{
    public function find(int $id, array $select,bool $getDeleted): Supplier;

    public function allByAgencyId(string $agencyId, array $with, array $select, string $order='id', string $direction='asc',bool $getDeleted=false) : Collection;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function create(array $data): Supplier;

    public function update(int $id, array $data): Supplier;

    public function updateField(int $supplierId, array $params) : bool;
    
    public function delete(int $id, bool $isSoftDelete): bool;

    public function getWhere(array $where, array $select = []) : Collection;

    public function getCodeById(int $id) : ?string;
}
