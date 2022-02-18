<?php

namespace App\Repositories\ReservePurchasingSubjectAirplane;

use App\Models\ReservePurchasingSubjectAirplane;
use Illuminate\Support\Collection;

interface ReservePurchasingSubjectAirplaneRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReservePurchasingSubjectAirplane;

  public function create(array $data) : ReservePurchasingSubjectAirplane;

  public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectAirplane;

  public function deleteOtherIdsForSchedule(int $reserveScheduleId, array $notDeleteIds, bool $isSoftDelete = true) : array;
}
