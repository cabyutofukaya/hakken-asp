<?php

namespace App\Traits;

/**
 * Area用のサジェストselectメニューを扱うtrait
 */
trait AreaSuggestTrait
{
    /**
     * selectメニューで使用するフォーマット配列を取得
     */
    public function getSelectRow($id, $code, $name) : array
    {
        return ['label' => "{$code}{$name}", 'value' => $id];
    }
}
