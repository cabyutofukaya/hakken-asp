<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Lang;

/**
 * 定数データを扱うtrait
 */
trait ConstsTrait
{
    /**
     * TODO これ廃止する方向
     * カスタムフィールドタイプ一覧（主にreactに渡す用）
     */
    public function getCustomFieldTypes() : array
    {
        return [
            'text' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'),
            'list' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
            'date' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'),
        ];
    }

    /**
     * TODO これは廃止する予定
     * カスタムフィールドinputタイプ一覧（主にreactに渡す用）
     */
    public function getCustomFieldInputTypes() : array
    {
        return [
            'oneline' => config('consts.user_custom_items.INPUT_TYPE_TEXT_01'),
            'multiple' => config('consts.user_custom_items.INPUT_TYPE_TEXT_02'),
            'calendar' => config('consts.user_custom_items.INPUT_TYPE_DATE_01'),
            'time' => config('consts.user_custom_items.INPUT_TYPE_DATE_02'),
        ];
    }
}
