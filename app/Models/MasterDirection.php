<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDirection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'code',
        'name',
        'gen_key',
    ];

    public static function boot()
    {
        parent::boot();

        // リレーションが特殊なので削除時の処理はライブラリなどは使わずに自前実装
        static::deleting(function ($masterDirection) {
            // リレーションの参照先をnullに(master_areas,areas)
            $masterDirection->master_areas()->update([
                'master_direction_uuid' => null,
            ]);
            $masterDirection->areas()->update([
                'v_direction_uuid' => null,
            ]);
        });
    }

    // 国・地域（master_areasとのリレーション）
    public function master_areas()
    {
        return $this->hasMany('App\Models\MasterArea', 'master_direction_uuid', 'uuid');
    }

    // 国・地域（areasとのリレーション）
    public function areas()
    {
        return $this->hasMany('App\Models\Area', 'v_direction_uuid', 'uuid');
    }
}
