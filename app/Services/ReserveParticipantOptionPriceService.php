<?php

namespace App\Services;

use App\Traits\ParticipantPriceTrait;
use App\Models\ReserveParticipantOptionPrice;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\ReserveParticipantOptionPrice\ReserveParticipantOptionPriceRepository;

class ReserveParticipantOptionPriceService implements ReserveParticipantPriceInterface
{
    use ParticipantPriceTrait;

    public function __construct(ReserveParticipantOptionPriceRepository $reserveParticipantOptionPriceRepository)
    {
        $this->reserveParticipantOptionPriceRepository = $reserveParticipantOptionPriceRepository;
    }

    /**
     * 参加者IDに紐づくレコードを削除
     *
     * @param int $participantId 参加者ID
     * @param bool $ifExistWithdrawalDelete 出金データがあっても削除する場合はtrue
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete = true): bool
    {
        return $this->reserveParticipantOptionPriceRepository->deleteByParticipantId($participantId, $ifExistWithdrawalDelete, $isSoftDelete);
    }

    /**
     * 当該参加者のvalidカラムを更新
     */
    public function updateValidForParticipant(int $participantId, bool $valid) : bool
    {
        return $this->reserveParticipantOptionPriceRepository->updateByParticipantId($participantId, $valid);
    }

    /**
     * 当該reserve_purchasing_subject_option_idに紐づく出金履歴が存在する場合はtrue
     *
     * @param int $reservePurchasingSubjectOptionId
     * @return bool
     */
    public function existWithdrawalHistoryByReservePurchasingSubjectOptionId(int $reservePurchasingSubjectOptionId)
    {
        return $this->reserveParticipantOptionPriceRepository->existWithdrawalHistoryByReservePurchasingSubjectOptionId($reservePurchasingSubjectOptionId);
    }

    /**
     * 当該予約IDに紐づく仕入データがある場合はtrue
     */
    public function isExistsDataByReserveId(int $reserveId, bool $getDeleted = false) : bool
    {
        return $this->reserveParticipantOptionPriceRepository->whereExists(['reserve_id' => $reserveId], $getDeleted);
    }

    /**
     * 当該予約IDに紐づく仕入一覧を取得
     *
     * @param bool $isValid 有効・無効フラグ。Nullの場合は指定ナシ
     */
    public function getByReserveId(int $reserveId, ?bool $isValid = null, array $with = [], array $select = [], bool $getDeleted = false) : Collection
    {
        $param = !is_null($isValid) ? ['valid' => $isValid, 'reserve_id' => $reserveId] : ['reserve_id' => $reserveId]; // $isValidパラメータが指定されている場合は検索条件に付与

        return $this->reserveParticipantOptionPriceRepository->getWhere($param, $with, $select, $getDeleted);
    }

    /**
     * 対象IDのキャンセルチャージ料金、キャンセルフラグを保存
     *
     * @param int $cancelCharge キャンセルチャージ金額
     * @param int $cancelChargeNet 仕入れ先支払料金
     * @param bool $isCancel キャンセル有無
     */
    public function setCancelChargeByIds(int $cancelCharge, int $cancelChargeNet, bool $isCancel, array $ids) : bool
    {
        return $this->reserveParticipantOptionPriceRepository->updateIds(['cancel_charge' => $cancelCharge, 'cancel_charge_net' => $cancelChargeNet, 'is_cancel' => $isCancel], $ids);
    }

    /**
     * 対象予約IDのキャンセルチャージ料金、キャンセルフラグを保存
     */
    public function setCancelChargeByReserveId(int $cancelCharge, int $cancelChargeNet, bool $isCancel, int $reserveId) : bool
    {
        return $this->reserveParticipantOptionPriceRepository->updateWhere(
            ['cancel_charge' => $cancelCharge, 'cancel_charge_net' => $cancelChargeNet, 'is_cancel' => $isCancel], 
            ['reserve_id' => $reserveId]
        );
    }
}
