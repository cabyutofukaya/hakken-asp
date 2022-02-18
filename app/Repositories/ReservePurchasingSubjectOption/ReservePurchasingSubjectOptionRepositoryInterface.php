<?php

namespace App\Repositories\ReservePurchasingSubjectOption;

use App\Models\ReservePurchasingSubjectOption;
use Illuminate\Support\Collection;

interface ReservePurchasingSubjectOptionRepositoryInterface
{
  public function create(array $data) : ReservePurchasingSubjectOption;

  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReservePurchasingSubjectOption;

  public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectOption;

  public function deleteOtherIdsForSchedule(int $reserveScheduleId, array $notDeleteIds, bool $isSoftDelete = true) : array;
}
