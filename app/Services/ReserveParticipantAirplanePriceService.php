<?php

namespace App\Services;

use App\Traits\ParticipantPriceTrait;
use App\Models\ReserveParticipantAirplanePrice;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\ReserveParticipantAirplanePrice\ReserveParticipantAirplanePriceRepository;

class ReserveParticipantAirplanePriceService implements ReserveParticipantPriceInterface
{
    use ParticipantPriceTrait;

    public function __construct(ReserveParticipantAirplanePriceRepository $reserveParticipantAirplanePriceRepository)
    {
        $this->reserveParticipantAirplanePriceRepository = $reserveParticipantAirplanePriceRepository;
    }

    /**
     * 当該reserve_purchasing_subject_airplane_idに紐づく出金履歴が存在する場合はtrue
     *
     * @param int $reservePurchasingSubjectAirplaneId
     * @return bool
     */
    public function existWithdrawalHistoryByReservePurchasingSubjectAirplaneId(int $reservePurchasingSubjectAirplaneId)
    {
        return $this->reserveParticipantAirplanePriceRepository->existWithdrawalHistoryByReservePurchasingSubjectAirplaneId($reservePurchasingSubjectAirplaneId);
    }

    /**
     * 参加者IDに紐づくレコードを削除
     *
     * @param int $participantId 参加者ID
     * @param bool $ifExistWithdrawalDelete 出金データがあっても削除する場合はtrue
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool
    {
        return $this->reserveParticipantAirplanePriceRepository->deleteByParticipantId($participantId, $ifExistWithdrawalDelete, $isSoftDelete);
    }

    /**
     * 当該参加者のvalidカラムを更新
     */
    public function updateValidForParticipant(int $participantId, bool $valid) : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->updateByParticipantId($participantId, $valid);
    }

    /**
     * 当該予約IDに紐づく仕入データがある場合はtrue
     */
    public function isExistsDataByReserveItineraryId(?int $reserveItineraryId, bool $getDeleted = false) : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->whereExists(['reserve_itinerary_id' => $reserveItineraryId], $getDeleted);
    }

    /**
     * 当該行程IDに紐づく仕入一覧を取得
     *
     * @param bool $isValid 有効・無効フラグ。Nullの場合は指定ナシ
     */
    public function getByReserveItineraryId(int $reserveItineraryId, ?bool $isValid = null, array $with = [], array $select = [], bool $getDeleted = false) : Collection
    {
        $param = !is_null($isValid) ? ['valid' => $isValid, 'reserve_itinerary_id' => $reserveItineraryId] : ['reserve_itinerary_id' => $reserveItineraryId]; // $isValidパラメータが指定されている場合は検索条件に付与

        return $this->reserveParticipantAirplanePriceRepository->getWhere($param, $with, $select, $getDeleted);
    }

    /**
     * 対象IDのキャンセルチャージ料金、キャンセルフラグを保存
     * 
     * @param int $cancelCharge キャンセルチャージ金額
     * @param int $cancelChargeNet 仕入れ先支払料金
     * @param int $cancelChargeProfit キャンセルチャージ粗利
     * @param bool $isCancel キャンセル有無
     */
    public function setCancelChargeByIds(int $cancelCharge, int $cancelChargeNet, int $cancelChargeProfit, bool $isCancel, array $ids) : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->updateIds(['purchase_type' => config('consts.const.PURCHASE_CANCEL'),'cancel_charge' => $cancelCharge, 'cancel_charge_net' => $cancelChargeNet, 'cancel_charge_profit' => $cancelChargeProfit, 'is_cancel' => $isCancel], $ids);
    }

    /**
     * 対象行程IDのキャンセルチャージ料金、キャンセルフラグを保存
     * 
     * @return array 処理対象のレコードIDリスト
     */
    public function setCancelChargeByReserveItineraryId(int $cancelCharge, int $cancelChargeNet, int $cancelChargeProfit, bool $isCancel, int $reserveItineraryId) : array
    {
        $targetRows = $this->reserveParticipantAirplanePriceRepository->getWhere(['reserve_itinerary_id' => $reserveItineraryId], [], ['id']);

        $this->reserveParticipantAirplanePriceRepository->updateWhere(
            ['purchase_type' => config('consts.const.PURCHASE_CANCEL'), 'cancel_charge' => $cancelCharge, 'cancel_charge_net' => $cancelChargeNet, 'cancel_charge_profit' => $cancelChargeProfit, 'is_cancel' => $isCancel], 
            ['reserve_itinerary_id' => $reserveItineraryId]
        );

        return $targetRows->pluck("id")->toArray();
    }
}
