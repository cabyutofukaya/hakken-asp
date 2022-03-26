<?php

namespace App\Services;

use App\Events\ChangePaymentAmountEvent;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\AccountPayableDetailService;

/**
 * "ReserveParticipantXXXXPriceService系"サービスclass(ReserveParticipantOptionPriceService/ReserveParticipantAirplanePriceService/ReserveParticipantHotelPriceService)全てに対して作業する処理を提供するclass
 */
class ReserveParticipantPriceService
{
    public function __construct(ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, AccountPayableDetailService $accountPayableDetailService)
    {
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->accountPayableDetailService = $accountPayableDetailService;
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
     * 有効仕入(valid=true)行に対し、キャンセル設定フラグ(is_alive_cancel)をオンに。is_alive_cancel=trueの行は行程編集ページの「キャンセルした仕入」一覧にリストアップされる。ノーチャージキャンセル用
     */
    public function setIsAliveCancelByParticipantId(int $participantId) : bool
    {
        // オプション科目
        $this->reserveParticipantOptionPriceService->setIsAliveCancelByParticipantId($participantId);

        // 航空券科目
        $this->reserveParticipantAirplanePriceService->setIsAliveCancelByParticipantId($participantId);

        // ホテル科目
        $this->reserveParticipantHotelPriceService->setIsAliveCancelByParticipantId($participantId);

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
     * キャンセルチャージをリセット
     * 当該予約の有効行程に対して処理
     *
     * TODO
     * 本メソッド、処理が重すぎるようなら非同期で実行することも検討
     *
     * @param ?int $reserveItineraryId 行程ID(有効な行程が設定されていない場合nullが渡される可能性あり)
     */
    public function cancelChargeReset(?int $reserveItineraryId) : bool
    {
        if (!$reserveItineraryId) {
            return true;
        } // 処理ナシ

        // 当該行程IDのオプション科目・航空券科目・ホテル科目のキャンセルチャージをリセット
        $optionIds = $this->reserveParticipantOptionPriceService->setCancelChargeByReserveItineraryId(0, 0, 0, false, $reserveItineraryId); // オプション科目

        if ($optionIds) {
            foreach ($optionIds as $id) {
                // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
                $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantOptionPrice', $id, true);
        
                // ステータスと支払い残高計算
                event(new ChangePaymentAmountEvent($accountPayableDetailId));
            }
        }

        $airplaneIds = $this->reserveParticipantAirplanePriceService->setCancelChargeByReserveItineraryId(0, 0, 0, false, $reserveItineraryId); // 航空券科目

        if ($airplaneIds) {
            foreach ($airplaneIds as $id) {
                // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
                $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantAirplanePrice', $id, true);

                // ステータスと支払い残高計算
                event(new ChangePaymentAmountEvent($accountPayableDetailId));
            }
        }

        $hotelIds = $this->reserveParticipantHotelPriceService->setCancelChargeByReserveItineraryId(0, 0, 0, false, $reserveItineraryId); // ホテル科目

        if ($hotelIds) {
            foreach ($hotelIds as $id) {
                // 当該科目に紐づくの支払い情報レコードの仕入額をリセット
                $accountPayableDetailId = $this->accountPayableDetailService->setCancelChargeBySaleableId(0, 'App\Models\ReserveParticipantHotelPrice', $id, true);

                // ステータスと支払い残高計算
                event(new ChangePaymentAmountEvent($accountPayableDetailId));
            }
        }
        
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
    public function getPurchaseFormDataByParticipantId(int $participantId, ?int $reserveItineraryId, ?bool $isValid = null, bool $getDeleted = false) : array
    {
        $options = $this->reserveParticipantOptionPriceService->getByParticipantId($participantId, $reserveItineraryId, $isValid, ['reserve_purchasing_subject_option','reserve_purchasing_subject_option.supplier:id,name'], [], $getDeleted);

        $airplanes = $this->reserveParticipantAirplanePriceService->getByParticipantId($participantId, $reserveItineraryId, $isValid, ['reserve_purchasing_subject_airplane','reserve_purchasing_subject_airplane.supplier:id,name'], [], $getDeleted);

        $hotels = $this->reserveParticipantHotelPriceService->getByParticipantId($participantId, $reserveItineraryId, $isValid, ['reserve_purchasing_subject_hotel','reserve_purchasing_subject_hotel.supplier:id,name'], [], $getDeleted);

        return $this->getPurchaseFormData($options, $airplanes, $hotels);
    }

    /**
     * キャンセルチャージページで使う当該予約の仕入リストを(form項目に合わせたデータ形式で)取得
     *
     * @param bool $isValid validの指定。nullの場合は特に指定ナシ
     * @return array
     */
    public function getPurchaseFormDataByReserveItineraryId(int $reserveItineraryId, ?bool $isValid = null, bool $getDeleted = false) : array
    {
        $options = $this->reserveParticipantOptionPriceService->getByReserveItineraryId($reserveItineraryId, $isValid, ['reserve_purchasing_subject_option.supplier'], [], $getDeleted);

        $airplanes = $this->reserveParticipantAirplanePriceService->getByReserveItineraryId($reserveItineraryId, $isValid, ['reserve_purchasing_subject_airplane.supplier'], [], $getDeleted);

        $hotels = $this->reserveParticipantHotelPriceService->getByReserveItineraryId($reserveItineraryId, $isValid, ['reserve_purchasing_subject_hotel.supplier'], [], $getDeleted);

        return $this->getPurchaseFormData($options, $airplanes, $hotels);
    }

    /**
     * form用にまとめた仕入データ配列を取得
     */
    private function getPurchaseFormData($options, $airplanes, $hotels)
    {
        $list = []; // 結果配列

        $purchasingFields = [
            'id',
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

        $res = []; // 数量を求めるために同じ商品ごとに配列にまとめる
        foreach ($list as $l) {
            $key = sha1(serialize(collect($l)->except(['id'])->all())); // ID以外の値で比較
            if (!isset($res[$key])) {
                $res[$key] = [];
            }
            $res[$key][] = $l;
        }

        $result = [];
        foreach ($res as $key => $rows) {
            $tmp = [];

            $tmp['quantity'] = count($rows); // 数量

            foreach (['code','name','supplier_id','supplier_name','zei_kbn','commission_rate','valid','is_cancel'] as $col) { // 共通値
                $tmp[$col] = Arr::get($rows, "0.{$col}");
            }

            foreach (['gross_ex','gross','cost','net','gross_profit','cancel_charge','cancel_charge_net','cancel_charge_profit'] as $col) { // 合計を計算
                $tmp[$col] = collect($rows)->sum($col);
            }

            $key = sprintf("%s" . config('consts.const.CANCEL_CHARGE_DATA_DELIMITER') . "%s", Arr::get($rows, "0.subject"), implode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), collect($rows)->pluck('id')->all())); // [subject値]_[ID]_[ID]_.. の形式でキー値を作成

            $result[$key] = $tmp;
        }

        return $result;
    }
}
