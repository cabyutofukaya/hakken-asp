<?php

namespace App\Traits;

/**
 * カスタム項目(view)値を保存する際の共通処理
 */
trait VCustomValueTrait
{
    // 値ゲッター（inputタイプに応じて値を整形）
    public function getValAttribute($value)
    {
        if ($value) {
            if ($this->input_type === config('consts.user_custom_items.INPUT_TYPE_DATE_01')) { // カレンダー形式の場合は YYYY/MM/DD 形式に変換
                return date('Y/m/d', strtotime($value));
            } else {
                return $value;
            }
        }
        return $value;
    }

}
