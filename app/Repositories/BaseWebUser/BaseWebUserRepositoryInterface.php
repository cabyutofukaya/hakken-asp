<?php

namespace App\Repositories\BaseWebUser;

use App\Models\BaseWebUser;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseWebUserRepositoryInterface
{
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): BaseWebUser;
    
    public function update(int $id, array $data): BaseWebUser;

    public function updateField(int $id, array $params) : bool;
    
    public function paginate(array $params, int $limit, array $with=[], array $select = []) : LengthAwarePaginator;

    public function delete(int $id, bool $isSoftDelete): bool;
}
