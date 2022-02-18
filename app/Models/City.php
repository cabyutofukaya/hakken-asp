<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HashidsTrait, Sortable, SoftDeletes, ModelLogTrait;
    
    public $sortable = [
        'id',
        'code',
        'name',
        'v_area.name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'v_area_uuid',
        'code',
        'name',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // 自社登録した国・地域レコードとマスターの国・地域レコードの両方に紐づくリレーション
    public function v_area()
    {
        return $this->belongsTo('App\Models\VArea', 'v_area_uuid', 'uuid')->withDefault();
    }

    // 自社登録した国・地域レコードに紐づくリレーション
    public function area()
    {
        return $this->belongsTo('App\Models\Area', 'v_area_uuid', 'uuid')->withDefault();
    }

    // masterの国・地域レコードに紐づくリレーション
    public function master_area()
    {
        return $this->belongsTo('App\Models\MasterArea', 'v_area_uuid', 'uuid')->withDefault();
    }
}
