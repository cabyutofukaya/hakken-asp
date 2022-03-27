<?php

namespace App\Traits;

use App\Events\ChangePaymentAmountEvent;
use App\Models\Reserve;
use App\Models\ReserveItinerary;
use App\Models\Participant;
use Illuminate\Support\Arr;

/**
 * キャンセルチャージを扱うtrait
 */
trait CancelChargeTrait
{
    /**
     * 仕入情報を取得
     *
     * @param int $reserveItineraryId 対象行程ID
     */
    public function getPurchasingListByParticipant(int $participantId, ?int $reserveItineraryId, ?bool $isValid = null)
    {
        // 仕入情報を取得
        $purchasingList = $this->reserveParticipantPriceService->getPurchaseFormDataByParticipantId($participantId, $reserveItineraryId, $isValid);
        
        return $purchasingList;
    }

    /**
     * 仕入情報を取得
     */
    public function getPurchasingListByReserve(Reserve $reserve, ?bool $isValid = null)
    {
        // 有効行程から仕入情報を取得
        $purchasingList = $this->reserveParticipantPriceService->getPurchaseFormDataByReserveItineraryId($reserve->enabled_reserve_itinerary->id, $isValid);

        // キャンセルチャージを新たに設定(store時)する場合はis_cancelカラムはvalidで初期化
        if (!$reserve->cancel_charge) {
            foreach ($purchasingList as $key => $row) {
                $purchasingList[$key]['is_cancel'] = $row['valid'] ? 1 : 0;
            }
        }

        return $purchasingList;
    }

    /**
     * キャンセルチャージ料金を保存(参加者用)
     *
     * TODO
     * 本メソッド、処理が重すぎるようなら非同期で実行することも検討
     * @return 処理対象の参加者商品情報ID配列を返す
     */
    public function setParticipantCancelCharge(array $input) : array
    {
        $optionIds = [];
        $airplaneIds = [];
        $hotelIds = [];

        // キャンセルチャージ料金を保存
        foreach (Arr::get($input, 'rows', []) as $key => $row) { // $keyは [科目名]_(仕入ID_...)という形式
            $info = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $key);
    
            $subject = $info[0]; // $infoの1番目の配列は科目名
            $ids = array_slice($info, 1); // idリスト

            $isCancel = Arr::get($row, 'is_cancel') == 1;

            $cancelCharge = 0;
            $cancelChargeNet = 0;
            $cancelChargeProfit = 0;

            if ($isCancel) {
                try {
                    $cancelCharge = ($row['cancel_charge'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりの「キャンセルチャージ」を求める
                    $cancelChargeNet = ($row['cancel_charge_net'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりの「仕入先支払料金合計」を求める
                    $cancelChargeProfit = $cancelCharge - $cancelChargeNet;
                } catch (\Exception $e) {
                    \Log::debug($e);
                }
            }

            if ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目

                $this->reserveParticipantOptionPriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

                // 仕入先支払い情報を更新
                foreach ($ids as $id) {
                    $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantOptionPrice', $id, false);

                    $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantOptionPrice', 'saleable_id' => $id], [], ['id']);

                    // ステータスと支払い残高計算
                    if ($accountPayableDetail) {
                        event(new ChangePaymentAmountEvent($accountPayableDetail->id));
                    }
                }

                // 処理したIDを追記
                $optionIds = array_merge($optionIds, $ids);

            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空券科目

                $this->reserveParticipantAirplanePriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

                // 仕入先支払い情報を更新
                foreach ($ids as $id) {
                    $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantAirplanePrice', $id, false);

                    $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantAirplanePrice', 'saleable_id' => $id], [], ['id']);

                    // ステータスと支払い残高計算
                    if ($accountPayableDetail) {
                        event(new ChangePaymentAmountEvent($accountPayableDetail->id));
                    }
                }

                // 処理したIDを追記
                $airplaneIds = array_merge($airplaneIds, $ids);

            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目

                $this->reserveParticipantHotelPriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

                // 仕入先支払い情報を更新
                foreach ($ids as $id) {
                    $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantHotelPrice', $id, false);

                    $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantHotelPrice', 'saleable_id' => $id], [], ['id']);

                    // ステータスと支払い残高計算
                    if ($accountPayableDetail) {
                        event(new ChangePaymentAmountEvent($accountPayableDetail->id));
                    }
                }

                // 処理したIDを追記
                $hotelIds = array_merge($hotelIds, $ids);
            }
        }

        return [
            $optionIds,
            $airplaneIds,
            $hotelIds
        ];
    }

    /**
     * キャンセルチャージ料金を保存(予約用)
     *
     * TODO
     * 本メソッド、処理が重すぎるようなら非同期で実行することも検討
     */
    public function setReserveCancelCharge(array $input)
    {
        // キャンセルチャージ料金を保存
        foreach ($input['rows'] as $key => $row) { // $keyは [科目名]_(仕入ID_...)という形式
            $info = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $key);
    
            $subject = $info[0]; // $infoの1番目の配列は科目名
            $ids = array_slice($info, 1); // idリスト

            $isCancel = Arr::get($row, 'is_cancel') == 1;

            $cancelCharge = 0;
            $cancelChargeNet = 0;
            $cancelChargeProfit = 0;

            if ($isCancel) {
                try {
                    $cancelCharge = ($row['cancel_charge'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりの「キャンセルチャージ」を求める
                    $cancelChargeNet = ($row['cancel_charge_net'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりの「仕入先支払料金合計」を求める
                    $cancelChargeProfit = $cancelCharge - $cancelChargeNet;
                } catch (\Exception $e) {
                    \Log::debug($e);
                }
            }

            if ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目
                $this->reserveParticipantOptionPriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

                // 仕入先支払い情報を更新
                foreach ($ids as $id) {
                    $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantOptionPrice', $id, false);

                    $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantOptionPrice', 'saleable_id' => $id], [], ['id']);

                    // ステータスと支払い残高計算
                    if ($accountPayableDetail) {
                        event(new ChangePaymentAmountEvent($accountPayableDetail->id));
                    }
                }
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空券科目
                $this->reserveParticipantAirplanePriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

                // 仕入先支払い情報を更新
                foreach ($ids as $id) {
                    $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantAirplanePrice', $id, false);

                    $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantAirplanePrice', 'saleable_id' => $id], [], ['id']);

                    // ステータスと支払い残高計算
                    if ($accountPayableDetail) {
                        event(new ChangePaymentAmountEvent($accountPayableDetail->id));
                    }
                }
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目
                $this->reserveParticipantHotelPriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

                // 仕入先支払い情報を更新
                foreach ($ids as $id) {
                    $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantHotelPrice', $id, false);

                    $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantHotelPrice', 'saleable_id' => $id], [], ['id']);

                    // ステータスと支払い残高計算
                    if ($accountPayableDetail) {
                        event(new ChangePaymentAmountEvent($accountPayableDetail->id));
                    }
                }
            }
        }
    }

    /**
     * 有効行程の合計金額更新
     */
    public function refreshItineraryTotalAmount(ReserveItinerary $reserveItinerary)
    {
        $this->reserveItineraryService->refreshItineraryTotalAmount($reserveItinerary); // 有効行程の合計金額更新
    }
}
