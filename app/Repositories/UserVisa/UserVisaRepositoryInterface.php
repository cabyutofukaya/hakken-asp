<?php

namespace App\Repositories\UserVisa;

use App\Models\UserVisa;
use Illuminate\Support\Collection;

interface UserVisaRepositoryInterface
{
  public function find(int $id): ?UserVisa;

  public function getWhere(array $where, array $with=[], array $select=[]) : Collection;
  
  public function create(array $data): UserVisa;

  public function update(int $id, array $data): UserVisa;
  
  public function delete(int $id, bool $isSoftDelete): bool;

}

