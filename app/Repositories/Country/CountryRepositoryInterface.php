<?php

namespace App\Repositories\Country;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CountryRepositoryInterface
{
    public function all() : Collection;
}
