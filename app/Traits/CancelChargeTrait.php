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
    public function getPurchasingListByReserve(Reserve $reserve, array $participants, ?bool $isValid = null)
    {
        // 有効行程から仕入情報を取得
        $purchasingList = $this->reserveParticipantPriceService->getPurchaseFormDataByReserveItineraryId($reserve->enabled_reserve_itinerary->id, $participants, $isValid);

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
        /**バルクアップデート用のキャンセル料金料パラメータ */
        $options = []; // オプション科目
        $airplanes = []; // 航空券科目
        $hotels = []; // ホテル科目

        // キャンセルチャージ料金を保存
        foreach ($input['rows'] as $key => $row) { // $keyは [科目名]_(仕入ID_...)という形式
            $info = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $key);
    
            $subject = $info[0]; // $infoの1番目の配列は科目名
            // $ids = array_slice($info, 1); // idリスト

            $isCancel = Arr::get($row, 'is_cancel') == 1; // キャンセル料金有無のチェックボックス

            // バルクアップデート用のデータを用意
            if ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目
                foreach (Arr::get($row, 'participants', []) as $p) {
                    $tmp = [];
                    $tmp['id'] = Arr::get($p, 'id'); // reserve_participant_option_pricesのID
                    $tmp['purchase_type'] = config('consts.const.PURCHASE_CANCEL');
                    $tmp['is_cancel'] = $isCancel;
                    $tmp['cancel_charge'] = Arr::get($p, 'cancel_charge', 0);
                    $tmp['cancel_charge_net'] = Arr::get($p, 'cancel_charge_net', 0);
                    $tmp['cancel_charge_profit'] = Arr::get($p, 'cancel_charge_profit', 0);

                    $options[] = $tmp;
                }
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空券科目
                foreach (Arr::get($row, 'participants', []) as $p) {
                    $tmp = [];
                    $tmp['id'] = Arr::get($p, 'id'); // reserve_participant_airplane_pricesのID
                    $tmp['purchase_type'] = config('consts.const.PURCHASE_CANCEL');
                    $tmp['is_cancel'] = $isCancel;
                    $tmp['cancel_charge'] = Arr::get($p, 'cancel_charge', 0);
                    $tmp['cancel_charge_net'] = Arr::get($p, 'cancel_charge_net', 0);
                    $tmp['cancel_charge_profit'] = Arr::get($p, 'cancel_charge_profit', 0);

                    $airplanes[] = $tmp;
                }
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目
                foreach (Arr::get($row, 'participants', []) as $p) {
                    $tmp = [];
                    $tmp['id'] = Arr::get($p, 'id'); // reserve_participant_hotel_pricesのID
                    $tmp['purchase_type'] = config('consts.const.PURCHASE_CANCEL');
                    $tmp['is_cancel'] = $isCancel;
                    $tmp['cancel_charge'] = Arr::get($p, 'cancel_charge', 0);
                    $tmp['cancel_charge_net'] = Arr::get($p, 'cancel_charge_net', 0);
                    $tmp['cancel_charge_profit'] = Arr::get($p, 'cancel_charge_profit', 0);

                    $hotels[] = $tmp;
                }
            }
        }

        // キャンセル料金カラムをバルクアップデート
        if ($options) { // オプション科目
            $this->reserveParticipantOptionPriceService->updateBulk($options);

            // 仕入先支払レコードの金額情報を更新
            $params = [];
            foreach ($options as $r) {
                $tmp = [];
                $tmp['saleable_id'] = Arr::get($r, "id");
                $tmp['amount_billed'] = Arr::get($r, "cancel_charge_net", 0); // 支払い金額にキャンセルチャージ

                $params[] = $tmp;
            }
            $this->accountPayableDetailService->setAmountBilledBulk('App\Models\ReserveParticipantOptionPrice', $params, 'saleable_id'); // バルクアップデート

            // 仕入先支払レコードのステータスと支払い残高計算
            $ids = collect($options)->pluck("id")->all();
            foreach ($this->accountPayableDetailService->getIdsBySaleableIds('App\Models\ReserveParticipantOptionPrice', $ids) as $accountPayableDetailId) {
                event(new ChangePaymentAmountEvent($accountPayableDetailId));
            }
        }
        if ($airplanes) { // 航空券科目
            $this->reserveParticipantAirplanePriceService->updateBulk($airplanes);

            // 仕入先支払レコードの金額情報を更新
            $params = [];
            foreach ($options as $r) {
                $tmp = [];
                $tmp['saleable_id'] = Arr::get($r, "id");
                $tmp['amount_billed'] = Arr::get($r, "cancel_charge_net", 0); // 支払い金額にキャンセルチャージ

                $params[] = $tmp;
            }
            $this->accountPayableDetailService->setAmountBilledBulk('App\Models\ReserveParticipantAirplanePrice', $params, 'saleable_id'); // バルクアップデート

            // 仕入先支払レコードのステータスと支払い残高計算
            $ids = collect($options)->pluck("id")->all();
            foreach ($this->accountPayableDetailService->getIdsBySaleableIds('App\Models\ReserveParticipantAirplanePrice', $ids) as $accountPayableDetailId) {
                event(new ChangePaymentAmountEvent($accountPayableDetailId));
            }
        }
        if ($hotels) { // ホテル科目
            $this->reserveParticipantHotelPriceService->updateBulk($hotels);

            // 仕入先支払レコードの金額情報を更新
            $params = [];
            foreach ($options as $r) {
                $tmp = [];
                $tmp['saleable_id'] = Arr::get($r, "id");
                $tmp['amount_billed'] = Arr::get($r, "cancel_charge_net", 0); // 支払い金額にキャンセルチャージ

                $params[] = $tmp;
            }
            $this->accountPayableDetailService->setAmountBilledBulk('App\Models\ReserveParticipantHotelPrice', $params, 'saleable_id'); // バルクアップデート

            // 仕入先支払レコードのステータスと支払い残高計算
            $ids = collect($options)->pluck("id")->all();
            foreach ($this->accountPayableDetailService->getIdsBySaleableIds('App\Models\ReserveParticipantHotelPrice', $ids) as $accountPayableDetailId) {
                event(new ChangePaymentAmountEvent($accountPayableDetailId));
            }
        }
    }

    // /**
    //  * キャンセルチャージ料金を保存(予約用)
    //  *
    //  * TODO
    //  * 本メソッド、処理が重すぎるようなら非同期で実行することも検討
    //  */
    // public function setReserveCancelCharge(array $input)
    // {
    //     // キャンセルチャージ料金を保存
    //     foreach ($input['rows'] as $key => $row) { // $keyは [科目名]_(仕入ID_...)という形式
    //         $info = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $key);
    
    //         $subject = $info[0]; // $infoの1番目の配列は科目名
    //         $ids = array_slice($info, 1); // idリスト

    //         $isCancel = Arr::get($row, 'is_cancel') == 1; // キャンセル料金有無のチェックボックス

    //         $cancelCharge = 0;
    //         $cancelChargeNet = 0;
    //         $cancelChargeProfit = 0;

    //         if ($isCancel) {
    //             try {
    //                 $cancelCharge = ($row['cancel_charge'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりの「キャンセルチャージ」を求める
    //                 $cancelChargeNet = ($row['cancel_charge_net'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりの「仕入先支払料金合計」を求める
    //                 $cancelChargeProfit = $cancelCharge - $cancelChargeNet;
    //             } catch (\Exception $e) {
    //                 \Log::debug($e);
    //             }
    //         }

    //         if ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目
    //             $this->reserveParticipantOptionPriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

    //             // 仕入先支払い情報を更新
    //             foreach ($ids as $id) {
    //                 $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantOptionPrice', $id, false);

    //                 $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantOptionPrice', 'saleable_id' => $id], [], ['id']);

    //                 // ステータスと支払い残高計算
    //                 if ($accountPayableDetail) {
    //                     event(new ChangePaymentAmountEvent($accountPayableDetail->id));
    //                 }
    //             }
    //         } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空券科目
    //             $this->reserveParticipantAirplanePriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

    //             // 仕入先支払い情報を更新
    //             foreach ($ids as $id) {
    //                 $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantAirplanePrice', $id, false);

    //                 $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantAirplanePrice', 'saleable_id' => $id], [], ['id']);

    //                 // ステータスと支払い残高計算
    //                 if ($accountPayableDetail) {
    //                     event(new ChangePaymentAmountEvent($accountPayableDetail->id));
    //                 }
    //             }
    //         } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目
    //             $this->reserveParticipantHotelPriceService->setCancelChargeByIds($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $ids); // ユーザー側

    //             // 仕入先支払い情報を更新
    //             foreach ($ids as $id) {
    //                 $this->accountPayableDetailService->setCancelChargeBySaleableId($cancelChargeNet, 'App\Models\ReserveParticipantHotelPrice', $id, false);

    //                 $accountPayableDetail = $this->accountPayableDetailService->findWhere(['saleable_type' => 'App\Models\ReserveParticipantHotelPrice', 'saleable_id' => $id], [], ['id']);

    //                 // ステータスと支払い残高計算
    //                 if ($accountPayableDetail) {
    //                     event(new ChangePaymentAmountEvent($accountPayableDetail->id));
    //                 }
    //             }
    //         }
    //     }
    // }

    /**
     * 有効行程の合計金額更新
     */
    public function refreshItineraryTotalAmount(ReserveItinerary $reserveItinerary)
    {
        $this->reserveItineraryService->refreshItineraryTotalAmount($reserveItinerary); // 有効行程の合計金額更新
    }
}
