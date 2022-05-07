<?php

namespace App\Traits;

/**
 * 支払管理用trait
 */
trait PaymentTrait
{
    /**
     * @param int $unpaidAmount 未払金額
     * @param int $amountBilled 支払額(Net料金)
     * @param string 対象テーブル名
     */
    public function getPaymentStatus(int $unpaidAmount, int $amountBilled, string $tableName) : int
    {
        if ($unpaidAmount > 0) { // 支払い残高あり＝未払い
            $status = config("consts." . $tableName . ".STATUS_UNPAID");
        } elseif ($unpaidAmount < 0) { // 支払い残高がマイナス＝過払い
            $status = config("consts." . $tableName . ".STATUS_OVERPAID");
        } else { // 支払い残高0
            $status = $amountBilled ? config("consts." . $tableName . ".STATUS_PAID") : config("consts." . $tableName . ".STATUS_NONE"); // 請求金額がある場合は支払い済み。それ以外はNoneで初期化
        }

        return $status;
    }
}
