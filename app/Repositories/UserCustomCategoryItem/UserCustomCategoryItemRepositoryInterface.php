<?php

namespace App\Repositories\UserCustomCategoryItem;

use App\Models\UserCustomCategoryItem;
use Illuminate\Support\Collection;

interface UserCustomCategoryItemRepositoryInterface
{
  public function find(int $id): ?UserCustomCategoryItem;
  public function getDisplayPositionsForType(string $type) : Collection;
  public function findWhere(array $where) : ?UserCustomCategoryItem;
}

