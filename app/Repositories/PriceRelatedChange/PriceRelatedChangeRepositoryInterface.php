<?php

namespace App\Repositories\PriceRelatedChange;

use Illuminate\Support\Collection;
use App\Models\PriceRelatedChange;
use Illuminate\Pagination\LengthAwarePaginator;

interface PriceRelatedChangeRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []) : PriceRelatedChange;
}
