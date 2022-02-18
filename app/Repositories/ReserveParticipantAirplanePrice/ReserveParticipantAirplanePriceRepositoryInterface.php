<?php

namespace App\Repositories\ReserveParticipantAirplanePrice;

use App\Models\ReserveParticipantAirplanePrice;
use Illuminate\Support\Collection;

interface ReserveParticipantAirplanePriceRepositoryInterface
{
  public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool;
  public function getWhere(array $where, array $with=[], array $select=[]) : Collection;
  public function updateByParticipantId(int $participantId, bool $valid): bool;
  public function existWithdrawalHistoryByReservePurchasingSubjectAirplaneId(int $reservePurchasingSubjectAirplaneId) : bool;
}
