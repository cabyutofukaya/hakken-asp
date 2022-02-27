<?php

namespace App\Repositories\ReserveParticipantAirplanePrice;

use App\Models\ReserveParticipantAirplanePrice;
use Illuminate\Support\Collection;

interface ReserveParticipantAirplanePriceRepositoryInterface
{
  public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool;
  public function getWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : Collection;
  public function updateByParticipantId(int $participantId, bool $valid): bool;
  public function existWithdrawalHistoryByReservePurchasingSubjectAirplaneId(int $reservePurchasingSubjectAirplaneId) : bool;
  public function updateIds(array $update, array $ids) : bool;
  public function updateWhere(array $update, array $where) : bool;
}
