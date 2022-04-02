<?php

namespace App\Repositories\ReserveParticipantOptionPrice;

use App\Models\ReserveParticipantOptionPrice;
use Illuminate\Support\Collection;

interface ReserveParticipantOptionPriceRepositoryInterface
{
  public function updateByParticipantId(int $participantId, bool $valid): bool;

  public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool;

  public function existWithdrawalHistoryByReservePurchasingSubjectOptionId(int $reservePurchasingSubjectOptionId) : bool;

  public function existCancelByReservePurchasingSubjectOptionId(int $reservePurchasingSubjectOptionId) : bool;
  
  public function getWhere(array $where, array $with = [], array $select = [], bool $getDeleted = false): Collection;

  public function updateIds(array $update, array $ids) : bool;

  public function updateWhere(array $update, array $where) : bool;

  public function insert(array $params) : bool;

  public function updateBulk(array $params, string $id) : bool;

  public function whereExists(array $where, bool $getDeleted = false) : bool;
}
