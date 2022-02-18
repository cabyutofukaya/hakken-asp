<?php

namespace App\Traits;

/**
 * カスタム項目値を保存する際の共通処理
 */
trait CustomValueTrait
{
    // 値保存（inputタイプに応じて値を整形）
    public function getValAttribute($value)
    {
        if ($value) {
            if ($this->user_custom_item->input_type === config('consts.user_custom_items.INPUT_TYPE_DATE_01')) { // カレンダー形式の場合は YYYY/MM/DD 形式に変換
                return date('Y/m/d', strtotime($value));
            } else {
                return $value;
            }
        }
        return null;
    }

    // 値保存（inputタイプに応じて値を整形）
    public function setValAttribute($value)
    {
        if (is_empty($value)) {
            $this->attributes['val'] = null;
        } else {
            if ($this->user_custom_item->input_type === config('consts.user_custom_items.INPUT_TYPE_DATE_01')) { // カレンダー形式の場合は YYYY-MM-DD 形式で保存する
                $this->attributes['val'] = date('Y-m-d', strtotime($value));
            } else {
                $this->attributes['val'] = $value;
            }
        }
    }
}
