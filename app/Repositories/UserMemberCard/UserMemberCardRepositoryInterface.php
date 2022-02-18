<?php

namespace App\Repositories\UserMemberCard;

use App\Models\UserMemberCard;
use Illuminate\Support\Collection;

interface UserMemberCardRepositoryInterface
{
  public function find(int $id): ?UserMemberCard;

  public function getWhere(array $where, array $with=[], array $select=[]) : Collection;

  public function create(array $data): UserMemberCard;

  public function update(int $id, array $data): UserMemberCard;

  public function delete(int $id, bool $isSoftDelete): bool;
}

