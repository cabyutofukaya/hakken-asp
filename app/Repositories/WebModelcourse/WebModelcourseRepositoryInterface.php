<?php

namespace App\Repositories\WebModelcourse;

use App\Models\WebModelcourse;
use Illuminate\Pagination\LengthAwarePaginator;

interface WebModelcourseRepositoryInterface
{
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebModelcourse;

    public function updateOrCreate(array $attributes, array $values = []) : WebModelcourse;

    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function getCount(int $agencyId, bool $includDeleted = true) : int;

    public function getValidCountByAuthorId(int $authorId) : int;
    
    public function updateFields(int $id, array $params) : bool;

    public function delete(int $id, bool $isSoftDelete): bool;
}
