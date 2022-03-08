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
     * キャンセルチャージをリセット
     * 当該予約の有効行程に対して処理
     *
     * TODO
     * 本メソッド、処理が重すぎるようなら非同期で実行することも検討
     * 
     * @param int $reserveItineraryId 行程ID
     */
    public function cancelChargeReset(int $reserveItineraryId) : bool
    {

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
     * 当該行程IDに紐づく仕入データがある場合はtrue
     * (getPurchaseFormDataByReserveItineraryId メソッドと違い、実際のリストを取得するのではなく値があるかどうかをチェックしたバージョン)
     */
    public function isExistsPurchaseDataByReserveItineraryId(?int $reserveItineraryId, bool $getDeleted = false) : bool
    {
        $res1 = $this->reserveParticipantOptionPriceService->isExistsDataByReserveItineraryId($reserveItineraryId, $getDeleted); // オプション科目

        $res2 = $this->reserveParticipantAirplanePriceService->isExistsDataByReserveItineraryId($reserveItineraryId, $getDeleted); // 航空券科目

        $res3 = $this->reserveParticipantHotelPriceService->isExistsDataByReserveItineraryId($reserveItineraryId, $getDeleted); // ホテル科目

        return $res1 || $res2 || $res3;
    }

    /**
     * キャンセルチャージページで使う当該予約の仕入リストを(form項目に合わせたデータ形式で)取得
     *
     * @return array
     */
    public function getPurchaseFormDataByReserveItineraryId(int $reserveItineraryId, bool $getDeleted = false) : array
    {
        $options = $this->reserveParticipantOptionPriceService->getByReserveItineraryId($reserveItineraryId, null, ['reserve_purchasing_subject_option.supplier'], [], $getDeleted); // is_validの値に関係なく全て取得

        $airplanes = $this->reserveParticipantAirplanePriceService->getByReserveItineraryId($reserveItineraryId, null, ['reserve_purchasing_subject_airplane.supplier'], [], $getDeleted); // is_validの値に関係なく全て取得

        $hotels = $this->reserveParticipantHotelPriceService->getByReserveItineraryId($reserveItineraryId, null, ['reserve_purchasing_subject_hotel.supplier'], [], $getDeleted); // is_validの値に関係なく全て取得

        $list = []; // 結果配列

        $purchasingFields = ['id','valid','gross_ex','gross','cost','commission_rate','net','zei_kbn','gross_profit','cancel_charge','cancel_charge_net','is_cancel'];

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

            foreach (['gross_ex','gross','cost','commission_rate','net','gross_profit','cancel_charge','cancel_charge_net'] as $col) { // 合計を計算
                $tmp[$col] = collect($rows)->sum($col);
            }

            $key = sprintf("%s" . config('consts.const.CANCEL_CHARGE_DATA_DELIMITER') . "%s", Arr::get($rows, "0.subject"), implode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), collect($rows)->pluck('id')->all())); // [subject値]_[ID]_[ID]_.. の形式でキー値を作成

            $result[$key] = $tmp;
        }

        return $result;
    }
}
