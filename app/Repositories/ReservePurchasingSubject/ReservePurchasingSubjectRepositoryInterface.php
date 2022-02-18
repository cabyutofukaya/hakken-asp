<?php

namespace App\Repositories\ReservePurchasingSubject;

use App\Models\ReservePurchasingSubject;
use Illuminate\Support\Collection;

interface ReservePurchasingSubjectRepositoryInterface
{
  public function create(array $data) : ReservePurchasingSubject;

  public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubject;

  public function existWithdrawalHistoryByReserveScheduleId(int $reserveScheduleId) : bool;

  public function deleteOtherSubjectableIdsForSchedule(int $reserveScheduleId, string $subjectableType, array $notDeleteSubjectableIds, bool $isSoftDelete = true) : bool;
}
