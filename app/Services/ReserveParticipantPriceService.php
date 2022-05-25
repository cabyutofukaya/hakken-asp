<?php

namespace App\Services;

use App\Events\ChangePaymentDetailAmountEvent;
use App\Events\ChangePaymentReserveAmountEvent;
use App\Events\ChangePaymentItemAmountEvent;
use App\Models\Participant;
use App\Models\Reserve;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\AccountPayableDetailService;
use App\Services\AccountPayableReserveService;
use App\Services\AgencyWithdrawalService;
use App\Traits\PaymentTrait;

/**
 * "ReserveParticipantXXXXPriceService系"サービスclass(ReserveParticipantOptionPriceService/ReserveParticipantAirplanePriceService/ReserveParticipantHotelPriceService)全てに対して作業する処理を提供するclass
 */
class ReserveParticipantPriceService
{
    use PaymentTrait;

    public function __construct(ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, AccountPayableDetailService $accountPayableDetailService, AccountPayableReserveService $accountPayableReserveService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->accountPayableReserveService = $accountPayableReserveService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
    }

    /**
     * 当該参加者の仕入データを一括変更
     *
     * @param int $participantId 参加者ID
     * @param int $cancelCharge キャンセルチャージ請求額
     * @param int $cancelChargeNet キャンセルチャージ支払い額
     * @param int $cancelChargeProfit キャンセルチャージ粗利
     * @param bool $isCancel キャンセル料の有効有無
     * @return bool
     */
    public function setCancelDataByParticipantId(int $participantId, int $cancelCharge, int $cancelChargeNet, int $cancelChargeProfit, bool $isCancel) : bool
    {
        // オプション科目
        $this->reserveParticipantOptionPriceService->setCancelChargeByParticipantId($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $participantId);

        // 航空券科目
        $this->reserveParticipantAirplanePriceService->setCancelChargeByParticipantId($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $participantId);

        // ホテル科目
        $this->reserveParticipantHotelPriceService->setCancelChargeByParticipantId($cancelCharge, $cancelChargeNet, $cancelChargeProfit, $isCancel, $participantId);

        return true;
    }

    /**
     * 当該参加者行に対し、キャンセル設定フラグ(is_alive_cancel)をオンに。is_alive_cancel=trueの行は行程編集ページの「キャンセルした仕入」一覧にリストアップされる。
     * キャンセル仕入行用。ノーチャージキャンセル用
     */
    public function setIsAliveCancelByParticipantIdForPurchaseCancel(int $participantId) : bool
    {
        // オプション科目
        $this->reserveParticipantOptionPriceService->setIsAliveCancelByParticipantIdForPurchaseCancel($participantId);

        // 航空券科目
        $this->reserveParticipantAirplanePriceService->setIsAliveCancelByParticipantIdForPurchaseCancel($participantId);

        // ホテル科目
        $this->reserveParticipantHotelPriceService->setIsAliveCancelByParticipantIdForPurchaseCancel($participantId);

        return true;
    }

    /**
     * 当該参加者仕入情報IDのキャンセル設定フラグ(is_alive_cancel)をオンに。is_alive_cancel=trueの行は行程編集ページの「キャンセルした仕入」一覧にリストアップされる。キャンセルチャージ処理用
     */
    public function setIsAliveCancelByReserveParticipantPriceIds(array $optionIds = [], array $airplaneIds = [], array $hotelIds = []) : bool
    {
        // オプション科目
        $this->reserveParticipantOptionPriceService->setIsAliveCancelByIds($optionIds);

        // 航空券科目
        $this->reserveParticipantAirplanePriceService->setIsAliveCancelByIds($airplaneIds);

        // ホテル科目
        $this->reserveParticipantHotelPriceService->setIsAliveCancelByIds($hotelIds);

        return true;
    }

    /**
     * 当該予約に紐づく有効仕入(valid=true)行に対し、キャンセル設定フラグ(is_alive_cancel)をオンに。is_alive_cancel=trueの行は行程編集ページの「キャンセルした仕入」一覧にリストアップされる。ノーチャージキャンセル用。
     * 引数には念の為、行程IDも渡す
     *
     * @param int $reserveId 予約ID
     * @param int $reserveItineraryId 行程ID
     */
    public function setIsAliveCancelByReserveId(int $reserveId, ?int $reserveItineraryId) : bool
    {
        if (!$reserveItineraryId) {
            return true;
        }
        
        // オプション科目
        $this->reserveParticipantOptionPriceService->setIsAliveCancelByReserveId($reserveId, $reserveItineraryId);

        // 航空券科目
        $this->reserveParticipantAirplanePriceService->setIsAliveCancelByReserveId($reserveId, $reserveItineraryId);

        // ホテル科目
        $this->reserveParticipantHotelPriceService->setIsAliveCancelByReserveId($reserveId, $reserveItineraryId);

        return true;
    }

    /**
     * ノンチャージキャンセルの支払情報更新。オプション科目用
     */
    private function noCancelChargeAccountPayableDetailForOption(array $optionIds)
    {
        foreach (array_chunk($optionIds, 1000) as $ids) { // 念の為1000件ずつ処理
            $params = [];
            // reserve_participant_option_priceのIDからaccount_payable_detailsレコードを取得
            foreach ($this->accountPayableDetailService->getBySaleableIds('App\Models\ReserveParticipantOptionPrice', $ids, ['v_agency_withdrawal_total:account_payable_detail_id,total_amount'], ['id']) as $row) {

                $withdrawalSum = data_get($row, 'v_agency_withdrawal_total.total_amount', 0); // 支払額

                // 更新パラメータ
                $tmp = [];
                $tmp['id'] = $row->id;
                $tmp['amount_billed'] = 0;//0円に
                $tmp['unpaid_balance'] = $tmp['amount_billed'] - $withdrawalSum; // 未払金額
                $tmp['status'] = $this->getPaymentStatus($tmp['unpaid_balance'], $tmp['amount_billed'], 'account_payable_details');

                $params[] = $tmp;
            }
            $this->accountPayableDetailService->updateBulk($params, "id");
        }
    }

    /**
     * ノンチャージキャンセルの支払情報更新。航空券科目用
     */
    private function noCancelChargeAccountPayableDetailForAirplane(array $airplaneIds)
    {
        foreach (array_chunk($airplaneIds, 1000) as $ids) { // 念の為1000件ずつ処理
            $params = [];
            // reserve_participant_airplane_priceのIDからaccount_payable_detailsレコードを取得
            foreach ($this->accountPayableDetailService->getBySaleableIds('App\Models\ReserveParticipantAirplanePrice', $ids, ['v_agency_withdrawal_total:account_payable_detail_id,total_amount'], ['id']) as $row) {

                $withdrawalSum = data_get($row, 'v_agency_withdrawal_total.total_amount', 0); // 支払額

                // 更新パラメータ
                $tmp = [];
                $tmp['id'] = $row->id;
                $tmp['amount_billed'] = 0;//0円に
                $tmp['unpaid_balance'] = $tmp['amount_billed'] - $withdrawalSum; // 未払金額
                $tmp['status'] = $this->getPaymentStatus($tmp['unpaid_balance'], $tmp['amount_billed'], 'account_payable_details');

                $params[] = $tmp;
            }
            $this->accountPayableDetailService->updateBulk($params, "id");
        }
    }

    /**
     * ノンチャージキャンセルの支払情報更新。ホテル科目用
     */
    private function noCancelChargeAccountPayableDetailForHotel(array $hotelIds)
    {
        foreach (array_chunk($hotelIds, 1000) as $ids) { // 念の為1000件ずつ処理
            $params = [];
            // reserve_participant_hotel_priceのIDからaccount_payable_detailsレコードを取得
            foreach ($this->accountPayableDetailService->getBySaleableIds('App\Models\ReserveParticipantHotelPrice', $ids, ['v_agency_withdrawal_total:account_payable_detail_id,total_amount'], ['id']) as $row) {

                $withdrawalSum = data_get($row, 'v_agency_withdrawal_total.total_amount', 0); // 支払額

                // 更新パラメータ
                $tmp = [];
                $tmp['id'] = $row->id;
                $tmp['amount_billed'] = 0;//0円に
                $tmp['unpaid_balance'] = $tmp['amount_billed'] - $withdrawalSum; // 未払金額
                $tmp['status'] = $this->getPaymentStatus($tmp['unpaid_balance'], $tmp['amount_billed'], 'account_payable_details');

                $params[] = $tmp;
            }
            $this->accountPayableDetailService->updateBulk($params, "id");
        }
    }

    /**
     * キャンセルチャージをリセット
     * 当該予約の有効行程に対して処理
     *
     * TODO
     * 本メソッド、処理が重すぎるようなら非同期で実行することも検討
     *
     * @param ?int $reserveItineraryId 行程ID(有効な行程が設定されていない場合nullが渡される可能性あり)
     */
    public function reserveNoCancelCharge(Reserve $reserve, ?int $reserveItineraryId) : bool
    {
        if (!$reserveItineraryId) {
            return true;
        } // 処理ナシ

        // 当該行程IDのオプション科目・航空券科目・ホテル科目のキャンセルチャージをリセット
        $optionIds = $this->reserveParticipantOptionPriceService->setCancelChargeByReserveItineraryId(0, 0, 0, false, $reserveItineraryId); // オプション科目

        if ($optionIds) {
            $this->noCancelChargeAccountPayableDetailForOption($optionIds);

            // foreach ($optionIds as $id) {
            //     // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
            //     $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantOptionPrice', $id, true);
        
            //     // ステータスと支払い残高計算
            //     event(new ChangePaymentDetailAmountEvent($accountPayableDetailId));
            // }
        }

        $airplaneIds = $this->reserveParticipantAirplanePriceService->setCancelChargeByReserveItineraryId(0, 0, 0, false, $reserveItineraryId); // 航空券科目

        if ($airplaneIds) {
            $this->noCancelChargeAccountPayableDetailForAirplane($airplaneIds);

            // foreach ($airplaneIds as $id) {
            //     // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
            //     $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantAirplanePrice', $id, true);

            //     // ステータスと支払い残高計算
            //     event(new ChangePaymentDetailAmountEvent($accountPayableDetailId));
            // }
        }

        $hotelIds = $this->reserveParticipantHotelPriceService->setCancelChargeByReserveItineraryId(0, 0, 0, false, $reserveItineraryId); // ホテル科目

        if ($hotelIds) {
            $this->noCancelChargeAccountPayableDetailForHotel($hotelIds);

            // foreach ($hotelIds as $id) {
            //     // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
            //     $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantHotelPrice', $id, true);

            //     // ステータスと支払い残高計算
            //     event(new ChangePaymentDetailAmountEvent($accountPayableDetailId));
            // }
        }

        // 当該予約の有効行程の仕入先＆商品毎のステータスと未払金額計算
        event(new ChangePaymentItemAmountEvent($reserve->enabled_reserve_itinerary->id));

        // 当該予約の支払いステータスと未払金額計算
        event(new ChangePaymentReserveAmountEvent($reserve));


        return true;
    }

    /**
     * 当該参加者をキャンセルチャージナシで更新
     */
    public function participantNoCancelCharge(Reserve $reserve, int $participantId) : bool
    {
        // オプション科目
        $optionIds = $this->reserveParticipantOptionPriceService->setCancelChargeByParticipantId(0, 0, 0, false, $participantId, true);

        if ($optionIds) {
            $this->noCancelChargeAccountPayableDetailForOption($optionIds);
            // foreach ($optionIds as $id) {
            //     // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
            //     $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantOptionPrice', $id, true);
        
            //     // ステータスと支払い残高計算
            //     event(new ChangePaymentDetailAmountEvent($accountPayableDetailId));
            // }
        }

        // 航空券科目
        $airplaneIds = $this->reserveParticipantAirplanePriceService->setCancelChargeByParticipantId(0, 0, 0, false, $participantId, true);

        if ($airplaneIds) {
            $this->noCancelChargeAccountPayableDetailForAirplane($airplaneIds);
            // foreach ($airplaneIds as $id) {
            //     // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
            //     $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantAirplanePrice', $id, true);

            //     // ステータスと支払い残高計算
            //     event(new ChangePaymentDetailAmountEvent($accountPayableDetailId));
            // }
        }

        // ホテル科目
        $hotelIds = $this->reserveParticipantHotelPriceService->setCancelChargeByParticipantId(0, 0, 0, false, $participantId, true);

        if ($hotelIds) {
            $this->noCancelChargeAccountPayableDetailForHotel($hotelIds);
            // foreach ($hotelIds as $id) {
            //     // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
            //     $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantHotelPrice', $id, true);

            //     // ステータスと支払い残高計算
            //     event(new ChangePaymentDetailAmountEvent($accountPayableDetailId));
            // }
        }

        // 当該予約の有効行程の仕入先＆商品毎のステータスと未払金額計算
        event(new ChangePaymentItemAmountEvent($reserve->enabled_reserve_itinerary->id));

        // 当該予約の支払いステータスと未払金額計算
        event(new ChangePaymentReserveAmountEvent($reserve));

        return true;
    }

    /**
     * 当該参加者IDに紐づく仕入データがある場合はtrue
     * (xxxx メソッドと違い、実際のリストを取得するのではなく値があるかどうかをチェックしたバージョン)
     */
    public function isExistsPurchaseDataByParticipantId(?int $participantId, ?bool $isValid = null, bool $getDeleted = false) : bool
    {
        $res1 = $this->reserveParticipantOptionPriceService->isExistsDataByParticipantId($participantId, $isValid, $getDeleted); // オプション科目
        if ($res1) {
            return true;
        }

        $res2 = $this->reserveParticipantAirplanePriceService->isExistsDataByParticipantId($participantId, $isValid, $getDeleted); // 航空券科目
        if ($res2) {
            return true;
        }

        $res3 = $this->reserveParticipantHotelPriceService->isExistsDataByParticipantId($participantId, $isValid, $getDeleted); // ホテル科目
        if ($res3) {
            return true;
        }

        return false;
    }

    /**
     * 当該行程IDに紐づく仕入データがある場合はtrue
     * (getPurchaseFormDataByReserveItineraryId メソッドと違い、実際のリストを取得するのではなく値があるかどうかをチェックしたバージョン)
     *
     * @param bool $isValid 対象とする仕入フラグ(valid)
     */
    public function isExistsPurchaseDataByReserveItineraryId(?int $reserveItineraryId, ?bool $isValid = null, bool $getDeleted = false) : bool
    {
        $res1 = $this->reserveParticipantOptionPriceService->isExistsDataByReserveItineraryId($reserveItineraryId, $isValid, $getDeleted); // オプション科目
        if ($res1) {
            return true;
        }

        $res2 = $this->reserveParticipantAirplanePriceService->isExistsDataByReserveItineraryId($reserveItineraryId, $isValid, $getDeleted); // 航空券科目
        if ($res2) {
            return true;
        }

        $res3 = $this->reserveParticipantHotelPriceService->isExistsDataByReserveItineraryId($reserveItineraryId, $isValid, $getDeleted); // ホテル科目
        if ($res3) {
            return true;
        }
        
        return false;
    }

    /**
     * キャンセルチャージページで使う当該参加者の仕入リストを(form項目に合わせたデータ形式で)取得
     *
     * @param bool $isValid validの指定。nullの場合は特に指定ナシ
     * @return array
     */
    public function getPurchaseFormDataByParticipantId(array $participant, ?int $reserveItineraryId, ?bool $isValid = null, bool $getDeleted = false) : array
    {
        $options = $this->reserveParticipantOptionPriceService->getByParticipantId(Arr::get($participant, 'participant_id'), $reserveItineraryId, $isValid, ['reserve_purchasing_subject_option.supplier:id,name,deleted_at'], [], $getDeleted);

        $airplanes = $this->reserveParticipantAirplanePriceService->getByParticipantId(Arr::get($participant, 'participant_id'), $reserveItineraryId, $isValid, ['reserve_purchasing_subject_airplane.supplier:id,name,deleted_at'], [], $getDeleted);

        $hotels = $this->reserveParticipantHotelPriceService->getByParticipantId(Arr::get($participant, 'participant_id'), $reserveItineraryId, $isValid, ['reserve_purchasing_subject_hotel.supplier:id,name,deleted_at'], [], $getDeleted);

        $purchaseFormData = $this->getPurchaseFormData($options, $airplanes, $hotels);

        // 集計した仕入情報に明細行をセット
        return $this->setDetailsToPurchaseFormData($purchaseFormData, [Arr::get($participant, 'participant_id') => $participant], $options, $airplanes, $hotels);
    }

    /**
     * キャンセルチャージページで使う当該予約の仕入リストを(form項目に合わせたデータ形式で)取得
     *
     * @param bool $isValid validの指定。nullの場合は特に指定ナシ
     * @return array
     */
    public function getPurchaseFormDataByReserveItineraryId(int $reserveItineraryId, array $participants, ?bool $isValid = null, bool $getDeleted = false) : array
    {
        $options = $this->reserveParticipantOptionPriceService->getByReserveItineraryId($reserveItineraryId, $isValid, ['reserve_purchasing_subject_option.supplier:id,name,deleted_at'], [], $getDeleted);

        $airplanes = $this->reserveParticipantAirplanePriceService->getByReserveItineraryId($reserveItineraryId, $isValid, ['reserve_purchasing_subject_airplane.supplier:id,name,deleted_at'], [], $getDeleted);

        $hotels = $this->reserveParticipantHotelPriceService->getByReserveItineraryId($reserveItineraryId, $isValid, ['reserve_purchasing_subject_hotel.supplier:id,name,deleted_at'], [], $getDeleted);

        $purchaseFormData = $this->getPurchaseFormData($options, $airplanes, $hotels);

        // 集計した仕入情報に明細行をセット
        return $this->setDetailsToPurchaseFormData($purchaseFormData, $participants, $options, $airplanes, $hotels);
    }

    /**
     * 集計した仕入情報に明細行をセット
     *
     * @param array $participants 参加者ID=>参加者データ 形式の配列
     */
    private function setDetailsToPurchaseFormData(array $purchaseFormData, array $participants, $options, $airplanes, $hotels)
    {
        // 集計した仕入情報に明細行をセット
        foreach ($purchaseFormData as $key => $pfd) {
            // $info = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $key);
    
            // $subject = $info[0]; // $infoの1番目の配列は科目名
            // $ids = array_slice($info, 1); // idリスト
    
            $subject = $pfd['subject'];
            $ids = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $pfd['ids']); // idリスト


            if ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目
                $rows = $options->whereIn('id', $ids);
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空券科目
                $rows = $airplanes->whereIn('id', $ids);
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目
                $rows = $hotels->whereIn('id', $ids);
            }

            $purchaseFormData[$key]['participants'] = [];
            foreach ($rows as $row) {
                // $tmp = Arr::except($row->toArray(), ['reserve_purchasing_subject_option','reserve_purchasing_subject_airplane','reserve_purchasing_subject_hotel']);
                $tmp = array_merge(
                    $participants[$row->participant_id],
                    Arr::except($row->toArray(), ['reserve_purchasing_subject_option','reserve_purchasing_subject_airplane','reserve_purchasing_subject_hotel'])
                );

                $purchaseFormData[$key]['participants'][] = $tmp; // 同一人物に同一商品が複数紐づいているケースがあるので同じ人が複数リストされるケースあり。同一人物でまとめてしまうと料金等は同じでも部屋番号やチケット番号などが異なる場合にまとめられないのでこの形式に
            }

            usort($purchaseFormData[$key]['participants'], function ($value1, $value2) {
                return intval($value1['participant_id']) - intval($value2['participant_id']);
            }); // みやすいように参加者ID順に並べ替え
        }

        return $purchaseFormData;
    }


    /**
     * form用にまとめた仕入データ配列を取得
     *
     */
    private function getPurchaseFormData($options, $airplanes, $hotels)
    {
        $list = []; // 結果配列

        $purchasingFields = [
            'id',
            'purchase_type', // 必要かどうか要検討
            'valid',
            'gross_ex',
            'gross',
            'cost',
            'commission_rate',
            'net',
            'zei_kbn',
            'gross_profit',
            'is_cancel',
            'cancel_charge',
            'cancel_charge_net',
            'cancel_charge_profit',
        ];

        // オプション科目
        foreach ($options as $row) {
            $tmp = [];

            $tmp['subject'] = config('consts.subject_categories.SUBJECT_CATEGORY_OPTION');
            $tmp['item_id'] = data_get(json_decode($row->reserve_purchasing_subject_option->name_ex), 'id');
            $tmp['code'] = optional($row->reserve_purchasing_subject_option)->code; // 商品コード
            $tmp['name'] = optional($row->reserve_purchasing_subject_option)->name; // 商品名
            ///////
            $tmp['supplier_id'] = optional($row->reserve_purchasing_subject_option)->supplier_id; // 仕入先ID
            $tmp['supplier_name'] = optional($row->reserve_purchasing_subject_option->supplier)->name; // 仕入先名
            ///////
            foreach ($purchasingFields as $col) {
                $tmp[$col] = $row->{$col};
            }
            $list[] = $tmp;
        }

        // 空港券科目
        foreach ($airplanes as $row) {
            $tmp = [];

            $tmp['subject'] = config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE');
            $tmp['item_id'] = data_get(json_decode($row->reserve_purchasing_subject_airplane->name_ex), 'id');
            $tmp['code'] = optional($row->reserve_purchasing_subject_airplane)->code; // 商品コード
            $tmp['name'] = optional($row->reserve_purchasing_subject_airplane)->name; // 商品名
            ///////
            $tmp['supplier_id'] = optional($row->reserve_purchasing_subject_airplane)->supplier_id; // 仕入先ID
            $tmp['supplier_name'] = optional($row->reserve_purchasing_subject_airplane->supplier)->name; // 仕入先名
            ///////
            foreach ($purchasingFields as $col) {
                $tmp[$col] = $row->{$col};
            }
            $list[] = $tmp;
        }

        // ホテル科目
        foreach ($hotels as $row) {
            $tmp = [];

            $tmp['subject'] = config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL');
            $tmp['item_id'] = data_get(json_decode($row->reserve_purchasing_subject_hotel->name_ex), 'id');
            $tmp['code'] = optional($row->reserve_purchasing_subject_hotel)->code; // 商品コード
            $tmp['name'] = optional($row->reserve_purchasing_subject_hotel)->name; // 商品名
            ///////
            $tmp['supplier_id'] = optional($row->reserve_purchasing_subject_hotel)->supplier_id; // 仕入先ID
            $tmp['supplier_name'] = optional($row->reserve_purchasing_subject_hotel->supplier)->name; // 仕入先名
            ///////
            foreach ($purchasingFields as $col) {
                $tmp[$col] = $row->{$col};
            }
            $list[] = $tmp;
        }

        // 仕分け基準フィールド。仕入科目、仕入コード、商品名、仕入先で比較 ←TODO これで良いか要確認。purchase_typeを含めないとリストは綺麗に纏まるが、例えばキャンセルチャージナシで個別にキャンセルした人がいた場合、仕入商品ごとにまとまってしまうとその設定が無視されてまとまってしまうので比較カラムとしておくべき。この基準はvalidとis_candelも同じ理屈。validについてはそもそもvalid=trueの条件で抽出しているのでvalid=falseのレコードは無い前提
        // $sortingCriteria = ['purchase_type','subject','code','name','supplier_id','supplier_name','valid','is_cancel'];

        // 仕分け基準フィールド。仕入先ID、仕入科目、商品IDで比較
        $sortingCriteria = ['purchase_type','subject','supplier_id','item_id','valid','is_cancel'];

        $common = [
            'code','name','supplier_name',
        ];

        $res = []; // 数量を求めるために同じ商品ごとに配列にまとめる
        foreach ($list as $l) {
            // $key = sha1(serialize(collect($l)->except(['id'])->all())); // ID以外の値で比較
            $key = sha1(serialize(collect($l)->only($sortingCriteria)->all())); //
            if (!isset($res[$key])) {
                $res[$key] = [];
            }
            $res[$key][] = $l;
        }

        $result = [];
        foreach ($res as $key => $rows) {
            $tmp = [];

            $tmp['quantity'] = count($rows); // 数量

            foreach ($sortingCriteria as $col) { // 仕分け基準
                $tmp[$col] = Arr::get($rows, "0.{$col}");
            }

            foreach ($common as $col) { // 共通値
                $tmp[$col] = Arr::get($rows, "0.{$col}");
            }

            // 合算できない数値系。税区分と手数料率は合算できないので全て同じ値であれば値をセット
            foreach (['zei_kbn','commission_rate'] as $col) {
                if (count(array_unique(collect($rows)->pluck($col)->all())) == 1) {
                    $tmp[$col] = Arr::get($rows, "0.{$col}");
                } else {
                    $tmp[$col] = ""; // 計算不可として処理
                }
            }

            foreach (['gross_ex','gross','cost','net','gross_profit','cancel_charge','cancel_charge_net','cancel_charge_profit'] as $col) { // 合計を計算
                $tmp[$col] = collect($rows)->sum($col);
            }

            // $rowKey = sprintf("%s" . config('consts.const.CANCEL_CHARGE_DATA_DELIMITER') . "%s", Arr::get($rows, "0.subject"), implode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), collect($rows)->pluck('id')->all())); // [subject値]_[ID]_[ID]_.. の形式でキー値を作成

            $tmp['ids'] = implode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), collect($rows)->pluck('id')->all()); // IDの値を区切り文字で区切って保存

            $result[] = $tmp;
        }

        return $result;
    }
}
