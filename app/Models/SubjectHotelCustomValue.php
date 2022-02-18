<?php

namespace App\Models;

use App\Traits\CustomValueTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ホテル科目カスタム項目の値を管理するテーブル
 */
class SubjectHotelCustomValue extends Model
{
    use ModelLogTrait,SoftDeletes,CustomValueTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject_hotel_id', 
        'user_custom_item_id', 
        'val',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
    
    public function subject_hotel()
    {
        return $this->belongsTo('App\Models\SubjectHotel')->withDefault();
    }

    public function user_custom_item()
    {
        return $this->belongsTo('App\Models\UserCustomItem')->withDefault();
    }
}
