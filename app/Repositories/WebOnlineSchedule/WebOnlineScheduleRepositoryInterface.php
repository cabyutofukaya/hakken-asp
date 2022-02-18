<?php

namespace App\Repositories\WebOnlineSchedule;

use App\Models\WebOnlineSchedule;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WebOnlineScheduleRepositoryInterface
{
  public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebOnlineSchedule;

  public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?WebOnlineSchedule;

  public function updateFields(int $id, array $params) : bool;

  public function deleteWhere(array $where, bool $isSoftDelete): bool;
  
  public function delete(int $id, bool $isSoftDelete): bool;
}
