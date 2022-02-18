<?php

namespace App\Repositories\ReserveTravelDate;

use App\Models\ReserveTravelDate;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveTravelDateRepositoryInterface
{
  public function getWhere(array $where, array $select = [], bool $getDeleted = false): Collection;

  public function deleteOtherIdsForReserveItinerary(int $reserveItineraryId, array $notDeleteIds, bool $isSoftDelete = true) : bool;

  public function deleteForItineraryDays(int $reserveItineraryId, array $deleteDays, bool $isSoftDelete = true) : bool;
}
