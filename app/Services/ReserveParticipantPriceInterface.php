<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface ReserveParticipantPriceInterface
{
    public function updateValidForParticipant(int $participantId, bool $valid) : bool;

    public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete = true): bool;

    public function isExistsDataByReserveId(int $reserveId, bool $getDeleted = false) : bool;
    
    public function getByReserveId(int $reserveId, ?bool $isValid = null, array $with = [], array $select = [], bool $getDeleted = false) : Collection;

    public function setCancelChargeByIds(int $cancelCharge, int $cancelChargeNet, bool $isCancel, array $ids) : bool;
}
