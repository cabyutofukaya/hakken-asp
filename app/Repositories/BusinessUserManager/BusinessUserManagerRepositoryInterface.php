<?php

namespace App\Repositories\BusinessUserManager;

use App\Models\BusinessUserManager;
use Illuminate\Support\Collection;

interface BusinessUserManagerRepositoryInterface
{
  public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): BusinessUserManager;

  public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?BusinessUserManager;

  public function getWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : Collection;
  
  public function allByAgencyId(string $agencyId, array $with, array $select, string $order='id', string $direction='asc') : Collection;

  public function applicantSearch(int $agencyId, ?string $name, ?string $userNumber, array $with = [], array $select=[], ?int $limit = null, bool $getDeleted = false) : Collection;
  
  public function create(array $data): BusinessUserManager;

  public function update(int $id, array $data): BusinessUserManager;
  
  public function delete(int $id, bool $isSoftDelete): bool;
}

