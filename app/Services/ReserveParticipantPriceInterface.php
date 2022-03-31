<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface ReserveParticipantPriceInterface
{
    public function updateValidForParticipant(int $participantId, bool $valid) : bool;

    public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete = true): bool;

    public function isExistsDataByParticipantId(int $participantId, ?bool $isValid = null, bool $getDeleted = false) : bool;

    public function isExistsDataByReserveItineraryId(?int $reserveItineraryId, ?bool $isValid = null, bool $getDeleted = false) : bool;
    
    public function getByReserveItineraryId(int $reserveItineraryId, ?bool $isValid = null, array $with = [], array $select = [], bool $getDeleted = false) : Collection;

    public function setCancelChargeByIds(int $cancelCharge, int $cancelChargeNet, int $cancelChargeProfit, bool $isCancel, array $ids) : bool;
    
    public function setIsAliveCancelByParticipantId(int $participantId) : bool;

    public function setIsAliveCancelByIds(array $ids) : bool;

    public function setIsAliveCancelByReserveId(int $reserveId, int $reserveItineraryId) : bool;

    public function setCancelChargeByReserveItineraryId(int $cancelCharge, int $cancelChargeNet, int $cancelChargeProfit, bool $isCancel, int $reserveItineraryId) : array;

    public function setCancelChargeByParticipantId(int $cancelCharge, int $cancelChargeNet, int $cancelChargeProfit, bool $isCancel, int $participantId) : bool;

    public function updateBulk(array $params) : bool;
}
