<?php

namespace App\Repositories\WebProfile;

use App\Models\WebProfile;

interface WebProfileRepositoryInterface
{
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebProfile;

    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?WebProfile;

    public function updateOrCreate(array $attributes, array $values = []) : WebProfile;

    public function updateFields(int $id, array $params) : bool;
}
