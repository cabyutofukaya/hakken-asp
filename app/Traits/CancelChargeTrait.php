<?php

namespace App\Traits;

use App\Models\Reserve;
use Illuminate\Support\Arr;

/**
 * キャンセルチャージを扱うtrait
 */
trait CancelChargeTrait
{
    /**
     * 仕入情報を取得
     */
    public function getPurchasingList(Reserve $reserve)
    {
        // 仕入の有効・無効に関係なく全ての仕入情報を引っ張る
        $purchasingList = $this->reserveParticipantPriceService->getPurchaseFormDataByReserveId($reserve->id);

        // キャンセルチャージを新たに設定(store時)する場合はis_cancelカラムはtrueで初期化
        if (!$reserve->cancel_charge) {
            foreach ($purchasingList as $key => $row) {
                $purchasingList[$key]['is_cancel'] = 1;
            }
        }

        return $purchasingList;
    }

    /**
     * キャンセルチャージ料金を保存
     */
    public function setCancelCharge(array $input)
    {
        // キャンセルチャージ料金を保存
        foreach ($input['rows'] as $key => $row) { // $keyは [科目名]_(仕入ID_...)という形式
            $info = explode(config('consts.const.CANCEL_CHARGE_DATA_DELIMITER'), $key);
    
            $subject = $info[0]; // $infoの1番目の配列は科目名
            $ids = array_slice($info, 1); // idリスト

            $cancelCharge = ($row['cancel_charge'] ?? 0) / $row['quantity']; // 数量で割って1商品あたりのキャンセルチャージを求める

            if ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) { // オプション科目
                $this->reserveParticipantOptionPriceService->setCancelChargeByIds($cancelCharge, Arr::get($row, 'is_cancel') == 1, $ids);
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) { // 航空券科目
                $this->reserveParticipantAirplanePriceService->setCancelChargeByIds($cancelCharge, Arr::get($row, 'is_cancel') == 1, $ids);
            } elseif ($subject == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) { // ホテル科目
                $this->reserveParticipantHotelPriceService->setCancelChargeByIds($cancelCharge, Arr::get($row, 'is_cancel') == 1, $ids);
            }
        }
    }
}
