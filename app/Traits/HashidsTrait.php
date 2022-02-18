<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

/**
 * Hashidsを扱うtrait
 * ルーティングで使用するIDは、Hashids でハッシュ化
 */
trait HashidsTrait
{
    // /**
    //  * エンコードされたIDを返す
    //  */
    // public function getIdAttribute($value) : string
    // {
    //     return Hashids::encode($value);
    // }

    // /**
    //  * デコードされたIDを返す
    //  */
    // public function getPureIdAttribute() : ?int
    // {
    //     return Hashids::decode($this->id)[0] ?? null;
    // }

    // // ID
    // public function getHashIdAttribute($value)
    // {
    //     return Hashids::encode($this->id);
    // }

    public function getRouteKey(): string
    {
        return Hashids::encode($this->getKey());
    }

    public function resolveRouteBinding($value): ?Model
    {
        $value = Hashids::decode($value)[0] ?? null;

        return $this->where($this->getRouteKeyName(), $value)->first();
    }
}
