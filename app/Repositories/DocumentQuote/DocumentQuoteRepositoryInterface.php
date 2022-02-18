<?php

namespace App\Repositories\DocumentQuote;

use App\Models\DocumentQuote;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DocumentQuoteRepositoryInterface
{
    public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false): DocumentQuote;

    public function paginateByAgencyId(int $agencyId, int $limit, array $with) : LengthAwarePaginator;
    
    public function findWhere(array $where, array $select = []) : ?DocumentQuote;

    public function getWhere(array $where, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc"): Collection;

    public function getAppendableTemplates(int $agencyId, array $nonAppendableCodes, array $select = [], bool $getDeleted = false, string $order = "seq", string $direction = "asc") : Collection;

    public function maxSeq(int $agencyId);

    public function create(array $data): DocumentQuote;

    public function update(int $id, array $data): DocumentQuote;

    public function delete(int $id, bool $isSoftDelete): bool;
}
