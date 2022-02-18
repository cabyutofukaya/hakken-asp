<?php

namespace App\Traits;

/**
 * HasManyリレーションで削除管理カラム(gen_key)
 * を扱うためのTrait
 */
trait HasManyGenTrait
{
    /**
      * 世代管理キーを生成
      */
    public function makeGenKey() : string
    {
        return md5(uniqid(rand()."", true));
    }
}
