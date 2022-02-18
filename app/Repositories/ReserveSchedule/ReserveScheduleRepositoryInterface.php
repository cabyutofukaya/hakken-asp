<?php

namespace App\Repositories\ReserveSchedule;

use App\Models\ReserveSchedule;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveScheduleRepositoryInterface
{
  public function deleteOtherIdsForTravelDate(int $reserveTravelDateId, array $notDeleteIds, bool $isSoftDelete = true) : bool;
  public function getWhere(array $where, array $with = [], array $select = [], bool $getDeleted = false): Collection;
}
