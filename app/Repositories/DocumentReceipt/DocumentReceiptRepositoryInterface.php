<?php

namespace App\Repositories\DocumentReceipt;

use App\Models\DocumentReceipt;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DocumentReceiptRepositoryInterface
{
    public function find(int $id, array $select = [], bool $getDeleted = false): DocumentReceipt;

    public function findWhere(array $where, array $select = [], $getDeleted = false) : ?DocumentReceipt;

    public function getWhere(array $where, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc"): Collection;

    public function paginateByAgencyId(int $agencyId, int $limit, array $with) : LengthAwarePaginator;

    public function maxSeq(int $agencyId);

    public function create(array $data): DocumentReceipt;

    public function update(int $id, array $data): DocumentReceipt;

    public function delete(int $id, bool $isSoftDelete): bool;
}
