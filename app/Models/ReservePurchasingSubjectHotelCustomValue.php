<?php

namespace App\Models;

use App\Traits\CustomValueTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 仕入科目のホテル項目値を管理するテーブル
 */
class ReservePurchasingSubjectHotelCustomValue extends Model
{
    use ModelLogTrait,SoftDeletes,CustomValueTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_purchasing_subject_hotel_id', 
        'user_custom_item_id', 
        'val',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
    
    public function reserve_purchasing_subject_hotel()
    {
        return $this->belongsTo('App\Models\ReservePurchasingSubjectHotel');
    }

    public function user_custom_item()
    {
        return $this->belongsTo('App\Models\UserCustomItem')->withDefault();
    }
}
