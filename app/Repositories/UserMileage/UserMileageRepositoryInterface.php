<?php

namespace App\Repositories\UserMileage;

use App\Models\UserMileage;
use Illuminate\Support\Collection;

interface UserMileageRepositoryInterface
{
  public function find(int $id): ?UserMileage;

  public function getWhere(array $where, array $with=[], array $select=[]) : Collection;

  public function create(array $data): UserMileage;

  public function update(int $id, array $data): UserMileage;

  public function delete(int $id, bool $isSoftDelete): bool;
}

