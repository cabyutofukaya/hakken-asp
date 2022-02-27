<?php

namespace App\Repositories\ReserveParticipantHotelPrice;

use App\Models\ReserveParticipantHotelPrice;
use Illuminate\Support\Collection;

interface ReserveParticipantHotelPriceRepositoryInterface
{
  public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool;

  public function getWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : Collection;

  public function updateByParticipantId(int $participantId, bool $valid): bool;

  public function existWithdrawalHistoryByReservePurchasingSubjectHotelId(int $reservePurchasingSubjectHotelId) : bool;

  public function updateIds(array $update, array $ids) : bool;

  public function updateWhere(array $update, array $where) : bool;
}
