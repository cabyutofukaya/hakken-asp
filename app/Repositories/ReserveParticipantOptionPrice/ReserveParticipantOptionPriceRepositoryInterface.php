<?php

namespace App\Repositories\ReserveParticipantOptionPrice;

use App\Models\ReserveParticipantOptionPrice;
use Illuminate\Support\Collection;

interface ReserveParticipantOptionPriceRepositoryInterface
{
  public function updateByParticipantId(int $participantId, bool $valid): bool;

  public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool;

  public function existWithdrawalHistoryByReservePurchasingSubjectOptionId(int $reservePurchasingSubjectOptionId) : bool;
}
