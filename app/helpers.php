<?php declare(strict_types=1);

use App\Models\UserCustomItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;

if (! function_exists('check_business_form_pdf_item')) {
    /**
     * $valueの配列に任意の値があるか(帳票PDF用)
     */
    function check_business_form_pdf_item($value, string $name, string $path) : bool
    {
        return is_array($value) && Arr::get($value, $path) && in_array($name, Arr::get($value, $path));
    }
}

if (! function_exists('get_webprofile_previewurl')) {
    /**
     * WebプロフィールのプレビューURLを取得
     */
    function get_webprofile_previewurl(string $staffHashId) : string
    {
        return sprintf("%s/meister/%s", env('HAKKEN_APP_URL'), $staffHashId);
    }
}

if (! function_exists('get_modelcourse_previewurl')) {
    /**
     * モデルコースのプレビューURLを取得
     */
    function get_modelcourse_previewurl(string $agencyAccount, string $courseNo) : string
    {
        return sprintf("%s/company/%s/modelcourse/%s", env('HAKKEN_APP_URL'), $agencyAccount, $courseNo);
    }
}

if (! function_exists('get_price_total')) {
    /**
     * オプション科目、航空券科目、ホテル科目の金額合計を計算
     *
     * @param array $participantIds 参加者IDリスト
     * @param array $optionPrices オプション科目料金リスト
     * @param array $airticketPrices 航空券科目料金リスト
     * @param array $hotelPrices ホテル科目料金リスト
     * @return int
     */
    function get_price_total(array $participantIds, ?array $optionPrices, ?array $airticketPrices, ?array $hotelPrices) : int
    {
        $amountTotal = 0;
        if ($optionPrices) {
            foreach ($optionPrices as $op) {
                if (in_array(Arr::get($op, 'participant_id'), $participantIds, true)) {
                    if (Arr::get($op, 'purchase_type') == config('consts.const.PURCHASE_NORMAL')) { // 通常仕入
                        $amountTotal += Arr::get($op, 'gross', 0);
                    } elseif (Arr::get($op, 'purchase_type') == config('consts.const.PURCHASE_CANCEL')) { // キャンセル仕入
                        $amountTotal += Arr::get($op, 'cancel_charge', 0);
                    }
                }
            }
        }
        if ($airticketPrices) {
            foreach ($airticketPrices as $ap) {
                if (in_array(Arr::get($ap, 'participant_id'), $participantIds, true)) {
                    if (Arr::get($ap, 'purchase_type') == config('consts.const.PURCHASE_NORMAL')) { // 通常仕入
                        $amountTotal += Arr::get($ap, 'gross', 0);
                    } elseif (Arr::get($ap, 'purchase_type') == config('consts.const.PURCHASE_CANCEL')) { // キャンセル仕入
                        $amountTotal += Arr::get($ap, 'cancel_charge', 0);
                    }
                }
            }
        }
        if ($hotelPrices) {
            foreach ($hotelPrices as $hp) {
                if (in_array(Arr::get($hp, 'participant_id'), $participantIds, true)) {
                    if (Arr::get($hp, 'purchase_type') == config('consts.const.PURCHASE_NORMAL')) { // 通常仕入
                        $amountTotal += Arr::get($hp, 'gross', 0);
                    } elseif (Arr::get($hp, 'purchase_type') == config('consts.const.PURCHASE_CANCEL')) { // キャンセル仕入
                        $amountTotal += Arr::get($hp, 'cancel_charge', 0);
                    }
                }
            }
        }
        return $amountTotal;
    }
}


if (! function_exists('get_reserve_price_total')) {
    /**
     * 先方担当者に紐づく予約金額の合計を計算
     *
     * @param array $partnerManagerIds 担当者IDリスト
     * @param array $reservePrices 料金内訳リスト（一括請求書作成で使っている内訳データ）
     * @parma array $reserveCancelInfo 未使用パラメータ
     * @return int
     */
    function get_reserve_price_total(array $partnerManagerIds, array $reservePrices, array $reserveCancelInfo) : int
    {
        $amountTotal = 0;
        foreach ($reservePrices as $managerId => $reserves) {
            if (in_array($managerId, $partnerManagerIds, true)) {
                foreach ($reserves as $reserveNumber => $zeiKbns) {
                    foreach ($zeiKbns as $zeiKbn => $rows) {
                        foreach ($rows as $row) {
                            if (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_NORMAL')) { // 通常仕入
                                $amountTotal += Arr::get($row, 'gross', 0);
                            } elseif (Arr::get($row, 'purchase_type') == config('consts.const.PURCHASE_CANCEL')) { // キャンセル仕入
                                $amountTotal += Arr::get($row, 'cancel_charge', 0);
                            }
                        }
                    }
                }
            }
        }
        return $amountTotal;
    }
}

if (! function_exists('get_const_item')) {
    /**
     * const配列を取得
     *
     * @param UserCustomItem $uci
     * @return string
     */
    function get_const_item($domain, $name) : array
    {
        $values = Lang::get("values.{$domain}.{$name}");
        $upperName = strtoupper($name);
        foreach (config("consts.{$domain}.{$upperName}_LIST") as $key => $val) {
            $data[$val] = Arr::get($values, $key);
        }
        return $data;
    }
}

if (! function_exists('get_uniqid')) {
    /**
     * ユニークIDを取得
     *
     * @return string
     */
    function get_uniqid(): string
    {
        // return md5(uniqid(rand()."", true));
        // return uniqid(rand()."");
        return uniqid();
    }
}

if (! function_exists('is_empty')) {
    /**
     * 値が空かを判別
     *
     * 0とfalseは除外
     *
     * @param string $var
     * @return bool
     */
    function is_empty($var=null): bool
    {
        if (empty($var) && 0 !== $var && '0' !== $var && false !== $var) {
            return true;
        } else {
            return false;
        }
    }
}
