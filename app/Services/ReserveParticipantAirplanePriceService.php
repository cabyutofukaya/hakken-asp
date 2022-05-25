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
     * 当該reserve_purchasing_subject_airplane_idに紐づくキャンセルレコードが存在する場合はtrue
     *
     * @param int $reservePurchasingSubjectAirplaneId
     * @return bool
     */
    public function existCancelByReservePurchasingSubjectAirplaneId(int $reservePurchasingSubjectAirplaneId)
    {
        return $this->reserveParticipantAirplanePriceRepository->existCancelByReservePurchasingSubjectAirplaneId($reservePurchasingSubjectAirplaneId);
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
     * 当該参加者IDに紐づく仕入データがある場合はtrue
     */
    public function isExistsDataByParticipantId(int $participantId, ?bool $isValid = null, bool $getDeleted = false) : bool
    {
        $param = !is_null($isValid) ? ['valid' => $isValid, 'participant_id' => $participantId] : ['participant_id' => $participantId]; // $isValidパラメータが指定されている場合は検索条件に付与

        return $this->reserveParticipantAirplanePriceRepository->whereExists($param, $getDeleted);
    }

    /**
     * 当該予約IDに紐づく仕入データがある場合はtrue
     */
    public function isExistsDataByReserveItineraryId(?int $reserveItineraryId, ?bool $isValid = null, bool $getDeleted = false) : bool
    {
        $param = !is_null($isValid) ? ['valid' => $isValid, 'reserve_itinerary_id' => $reserveItineraryId] : ['reserve_itinerary_id' => $reserveItineraryId]; // $isValidパラメータが指定されている場合は検索条件に付与

        return $this->reserveParticipantAirplanePriceRepository->whereExists($param, $getDeleted);
    }

    /**
     * 当該参加者ID&行程IDに紐づく仕入一覧を取得
     *
     * @param bool $isValid 有効・無効フラグ。Nullの場合は指定ナシ
     */
    public function getByParticipantId(int $participantId, ?int $reserveItineraryId, ?bool $isValid = null, array $with = [], array $select = [], bool $getDeleted = false) : Collection
    {
        $param = !is_null($isValid) ? ['valid' => $isValid, 'participant_id' => $participantId, 'reserve_itinerary_id' => $reserveItineraryId] : ['participant_id' => $participantId, 'reserve_itinerary_id' => $reserveItineraryId]; // $isValidパラメータが指定されている場合は検索条件に付与

        return $this->reserveParticipantAirplanePriceRepository->getWhere($param, $with, $select, $getDeleted);
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
     * @param int $cancelChargeNet 仕入先支払料金合計
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
        // 処理対象は当該行程の”通常仕入レコード(purchase_type=PURCHASE_NORMAL)”。全てのレコードをリセットしてしまうとキャンセル済みレコードの情報までリセットされてしまうので注意
        $targetRows = $this->reserveParticipantAirplanePriceRepository->getWhere(['reserve_itinerary_id' => $reserveItineraryId, 'purchase_type' => config('consts.const.PURCHASE_NORMAL')], [], ['id']);

        $this->reserveParticipantAirplanePriceRepository->updateWhere(
            ['purchase_type' => config('consts.const.PURCHASE_CANCEL'), 'cancel_charge' => $cancelCharge, 'cancel_charge_net' => $cancelChargeNet, 'cancel_charge_profit' => $cancelChargeProfit, 'is_cancel' => $isCancel],
            ['reserve_itinerary_id' => $reserveItineraryId, 'purchase_type' => config('consts.const.PURCHASE_NORMAL')]
        );

        return $targetRows->pluck("id")->toArray();
    }

    /**
     * 対象参加者IDのキャンセルチャージ料金、キャンセルフラグを保存
     *
     */
    public function setCancelChargeByParticipantId(int $cancelCharge, int $cancelChargeNet, int $cancelChargeProfit, bool $isCancel, int $participantId, bool $getIds = false) : ?array
    {
        $res = null;

        if ($getIds) {
            $res = $this->reserveParticipantAirplanePriceRepository->getWhere(['participant_id' => $participantId], [], ['id'])->pluck("id")->toArray();
        }

        $this->reserveParticipantAirplanePriceRepository->updateWhere(
            ['purchase_type' => config('consts.const.PURCHASE_CANCEL'), 'cancel_charge' => $cancelCharge, 'cancel_charge_net' => $cancelChargeNet, 'cancel_charge_profit' => $cancelChargeProfit, 'is_cancel' => $isCancel],
            ['participant_id' => $participantId]
        );

        return $res;
    }

    /**
     * キャンセル済み仕入行に対し、キャンセル設定フラグ(is_alive_cancel)をオンに。is_alive_cancel=trueの行は行程編集ページの「キャンセルした仕入」一覧にリストアップされる
     */
    public function setIsAliveCancelByParticipantIdForPurchaseCancel(int $participantId) : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->updateWhere(
            ['is_alive_cancel' => true],
            ['participant_id' => $participantId, 'purchase_type' => config('consts.const.PURCHASE_CANCEL')]
        );
    }

    /**
     * 当該IDのキャンセル設定フラグ(is_alive_cancel)をオンに。is_alive_cancel=trueの行は行程編集ページの「キャンセルした仕入」一覧にリストアップされる。キャンセルチャージ処理用
     */
    public function setIsAliveCancelByIds(array $ids) : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->updateIds(['is_alive_cancel' => true], $ids);
    }

    public function setIsAliveCancelByReserveId(int $reserveId, int $reserveItineraryId) : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->updateWhere(
            ['is_alive_cancel' => true, 'purchase_type' => config('consts.const.PURCHASE_CANCEL')],
            ['reserve_id' => $reserveId, 'reserve_itinerary_id' => $reserveItineraryId]
            // ['is_alive_cancel' => true],
            // ['reserve_id' => $reserveId, 'reserve_itinerary_id' => $reserveItineraryId, 'purchase_type' => config('consts.const.PURCHASE_CANCEL'), 'valid' => true]
        );
    }

    /**
     * バルクアップデート
     *
     * @param array $params
     */
    public function updateBulk(array $params, string $id = "id") : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->updateBulk($params, $id);
    }

    /**
     * バルクインサート
     */
    public function insert(array $params) : bool
    {
        return $this->reserveParticipantAirplanePriceRepository->insert($params);
    }
}
