<?php

namespace App\Repositories\WebCompany;

use App\Models\WebCompany;

interface WebCompanyRepositoryInterface
{
    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?WebCompany;

    public function updateOrCreate(array $attributes, array $values = []) : WebCompany;
}
