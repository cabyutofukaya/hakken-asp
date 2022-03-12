<?php

namespace App\Traits;

/**
 * 帳票モデルで使うtrait
 */
trait DocumentModelTrait
{
    /**
     * 出力項目設定
     */
    public function getSettingAttribute($value)
    {
        if ($value) { // settingの値は配列で初期化
            return array_map(function ($row) {
                return $row ?? [];
            }, json_decode($value, true));
        }
        return json_encode([], JSON_FORCE_OBJECT);
    }

    public function setSettingAttribute($value)
    {
        $this->attributes['setting'] = $value ? json_encode($value) : json_encode([]);
    }
}
