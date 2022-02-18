<?php

namespace App\Models;

use App\Traits\CustomValueTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 予約用カスタム項目の値を管理するテーブル
 */
class ReserveCustomValue extends Model
{
    use ModelLogTrait,SoftDeletes,CustomValueTrait;

    // timestamps連携
    protected $touches = ['reserve'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_id',
        'user_custom_item_id',
        'val',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
    
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
    }

    public function user_custom_item()
    {
        return $this->belongsTo('App\Models\UserCustomItem')->withDefault();
    }

    ////////////////// アクセサとミューテタ


}
