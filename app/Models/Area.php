<?php

namespace App\Models;

use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Area extends AgencyArea
{
    use HashidsTrait, Sortable, SoftDeletes, ModelLogTrait;
    
    public $sortable = [
        'id', 
        'code', 
        'name', 
        'name_en', 
        'direction.code',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'agency_id',
        'v_direction_uuid',
        'code',
        'name',
        'name_en',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();

        static::deleting(function ($area) {
            foreach ($area->cities as $area) {
                // リレーション先の参照をNULLに
                $area->v_area_uuid = null;
                $area->save();
            }
        });
        
    }

    // 自社登録した方向レコードに紐づくリレーション
    public function direction()
    {
        return $this->belongsTo('App\Models\Direction', 'v_direction_uuid', 'uuid')->withDefault();
    }

    // masterの方向レコードに紐づくリレーション
    public function master_direction()
    {
        return $this->belongsTo('App\Models\MasterDirection', 'v_direction_uuid', 'uuid')->withDefault();
    }

    // 自社登録の「都市・空港」
    public function cities()
    {
        return $this->hasMany('App\Models\City', 'v_area_uuid', 'uuid');
    }
}
