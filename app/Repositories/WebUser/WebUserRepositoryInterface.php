<?php

namespace App\Repositories\WebUser;

use App\Models\WebUser;
use Illuminate\Pagination\LengthAwarePaginator;

interface WebUserRepositoryInterface
{
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebUser;
    
    public function update(int $id, array $data): WebUser;

    public function updateField(int $id, array $params) : bool;
    
    public function paginate(array $params, int $limit, array $with=[], array $select = []) : LengthAwarePaginator;

    public function delete(int $id, bool $isSoftDelete): bool;
}
