<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use App\Traits\ParticipantPriceKbnTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class ReserveParticipantHotelPrice extends Model implements ParticipantPriceInterface
{
    use ModelLogTrait,SoftDeletes,ParticipantPriceKbnTrait,SoftCascadeTrait;

    // 行程管理テーブルupdated_at更新
    protected $touches = ['reserve_itinerary'];
    
    // ReservePurchasingSubjectHotelのbootメソッド内にて出金登録がある本モデルは消さないように制御しているので、softCascadeで削除されるレコードは出金登録がないものに限定される
    protected $softCascade = ['account_payable_detail'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_id',
        'reserve_itinerary_id',
        'reserve_purchasing_subject_hotel_id',
        'agency_id',
        'participant_id',
        'valid',
        'room_number',
        'gross_ex',
        'gross',
        'cost',
        'commission_rate',
        'net',
        'zei_kbn',
        'gross_profit',
        'cancel_charge',
        'cancel_charge_net',
        'is_cancel',
    ];

    protected $guarded = [
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'agency_id',
        'reserve_itinerary_id',
        'reserve_purchasing_subject_hotel_id',
        'updated_at',
        'created_at',
        'deleted_at',
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'gross_ex' => 'integer',
        'gross' => 'integer',
        'cost' => 'integer',
        'commission_rate' => 'integer',
        'net' => 'integer',
        'gross_profit' => 'integer',
        'cancel_charge' => 'integer',
        'cancel_charge_net' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
        
        // 削除時は「valid」フラグを念の為Offにしておく
        static::deleting(function ($row) {
            $row->valid = false;
            $row->save();
        });
    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 行程
    public function reserve_itinerary()
    {
        return $this->belongsTo('App\Models\ReserveItinerary')->withDefault();
    }

    // ホテル仕入科目
    public function reserve_purchasing_subject_hotel()
    {
        return $this->belongsTo('App\Models\ReservePurchasingSubjectHotel')->withDefault();
    }

    // 参加者
    public function participant()
    {
        return $this->belongsTo('App\Models\Participant')->withDefault();
    }

    // 仕入れ先買掛金明細
    public function account_payable_detail()
    {
        return $this->morphOne('App\Models\AccountPayableDetail', 'saleable');
    }

    //////// スコープ
    
    /**
     * 有効にチェックが入った仕入項目のみ対象
     */
    public function scopeIsValid($query)
    {
        return $query->where('valid', true);
    }
}
