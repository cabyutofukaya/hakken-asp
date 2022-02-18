<?php

namespace App\Models;

use Kyslik\ColumnSortable\Sortable;
use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direction extends AgencyDirection
{
    use HashidsTrait, Sortable, SoftDeletes, ModelLogTrait;
    
    public $sortable = ['id', 'code', 'name'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'agency_id',
        'code',
        'name',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();

        static::deleting(function ($direction) {
            foreach ($direction->areas as $area) {
                // リレーション先の参照をNULLに
                $area->v_direction_uuid = null;
                $area->save();
            }
        });
    }

    // 国・地域
    public function areas()
    {
        return $this->hasMany('App\Models\Area', 'v_direction_uuid', 'uuid');
    }
}
