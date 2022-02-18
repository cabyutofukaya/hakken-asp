<?php

namespace App\Traits;

/**
 * 帳票設定で使用
 */
trait DocumentTrait
{
    /**
     * settingの設定値を 「親値_子値」 の形式で取得
     */
    public function settingFlatAll($setting)
    {
        $res = [];
        foreach ($setting as $parent => $childs) {
            $res[] = $parent;
            foreach ($childs as $child) {
                $res[] = "{$parent}_{$child}";
            }
        }
        return $res;
    }
}
