<?php

namespace App\Services;

interface ReserveParticipantPriceInterface
{
    public function updateValidForParticipant(int $participantId, bool $valid) : bool;

    public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete = true): bool;
}
