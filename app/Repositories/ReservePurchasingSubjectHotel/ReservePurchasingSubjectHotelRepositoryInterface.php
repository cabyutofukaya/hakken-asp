<?php

namespace App\Repositories\ReservePurchasingSubjectHotel;

use App\Models\ReservePurchasingSubjectHotel;
use Illuminate\Support\Collection;

interface ReservePurchasingSubjectHotelRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReservePurchasingSubjectHotel;

  public function create(array $data) : ReservePurchasingSubjectHotel;

  public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectHotel;

  public function deleteOtherIdsForSchedule(int $reserveScheduleId, array $notDeleteIds, bool $isSoftDelete = true) : array;
}
