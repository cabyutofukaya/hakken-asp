<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterArea extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'code',
        'master_direction_uuid',
        'name',
        'name_en',
        'is_default',
        'gen_key',
    ];

    public static function boot()
    {
        parent::boot();

        // リレーションが特殊なので削除時の処理はライブラリなどは使わずに自前実装
        static::deleting(function ($masterArea) {
            // リレーションの参照先をnullに
            $masterArea->cities()->update([
                'v_area_uuid' => null,
            ]);
        });
    }

    // 都市・空港
    public function cities()
    {
        return $this->hasMany('App\Models\City', 'v_area_uuid', 'uuid');
    }
}
