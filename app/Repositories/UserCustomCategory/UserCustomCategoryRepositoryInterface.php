<?php

namespace App\Repositories\UserCustomCategory;

use App\Models\userCustomCategory;
use Illuminate\Support\Collection;

interface UserCustomCategoryRepositoryInterface
{
  public function find(int $id): UserCustomCategory;
  public function all(array $with, string $sort, string $direction): Collection;
  public function getListForCategoryItemType(string $type): Collection;
  public function findByCode(string $code) : ?UserCustomCategory;
}

