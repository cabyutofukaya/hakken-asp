<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class ReserveItinerary extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable,SoftCascadeTrait;

    // protected $touches = ['reserve'];

    // 料金レコードの集計に使用
    protected $with = [
        'reserve_participant_option_prices',
        'reserve_participant_hotel_prices', 
        'reserve_participant_airplane_prices',
    ]; // 参加者料金（オプション科目、ホテル科目、航空券科目）

    protected $appends = [
        'sum_gross_price',
        'sum_net_price',
        'sum_gross_profit_price',
    ];

    protected $softCascade = [
        'reserve_travel_dates',
        'reserve_confirms',
        'account_payables',
    ];

    public $sortable = [
        'created_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_id',
        'agency_id',
        'control_number',
        'enabled',
        'note',
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'enabled' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 予約
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
    }

    // 旅行日
    public function reserve_travel_dates()
    {
        return $this->hasMany('App\Models\ReserveTravelDate');
    }
    
    // 料金レコード
    public function reserve_participant_option_prices()
    {
        return $this->hasMany('App\Models\ReserveParticipantOptionPrice');
    }

    // ホテル科目料金レコード
    public function reserve_participant_hotel_prices()
    {
        return $this->hasMany('App\Models\ReserveParticipantHotelPrice');
    }

    // 航空券科目料金レコード
    public function reserve_participant_airplane_prices()
    {
        return $this->hasMany('App\Models\ReserveParticipantAirplanePrice');
    }

    // 予約確認書
    public function reserve_confirms()
    {
        return $this->hasMany('App\Models\ReserveConfirm');
    }

    // 仕入れ先買掛金
    public function account_payables()
    {
        return $this->hasMany('App\Models\AccountPayable');
    }

    ///////////////  集計メソッド ここから ///////////////

    /**
     * GROSS合計（オプション科目+航空券科目+ホテル科目）
     * valid=trueのみ集計
     */
    public function getSumGrossAttribute()
    {
        return $this->attributes['sum_gross_price'] = $this->reserve_participant_option_prices->where('valid', true)->sum('gross') + $this->reserve_participant_hotel_prices->where('valid', true)->sum('gross') + $this->reserve_participant_airplane_prices->where('valid', true)->sum('gross');
    }

    /**
     * NET合計（オプション科目+航空券科目+ホテル科目）
     * valid=trueのみ集計
     */
    public function getSumNetAttribute()
    {
        return $this->attributes['sum_net_price'] = $this->reserve_participant_option_prices->where('valid', true)->sum('net') + $this->reserve_participant_hotel_prices->where('valid', true)->sum('net') + $this->reserve_participant_airplane_prices->where('valid', true)->sum('net');
    }

    /**
     * 粗利合計（オプション科目+航空券科目+ホテル科目）
     * valid=trueのみ集計
     */
    public function getSumGrossProfitAttribute()
    {
        return $this->attributes['sum_gross_profit_price'] = $this->reserve_participant_option_prices->where('valid', true)->sum('gross_profit') + $this->reserve_participant_hotel_prices->where('valid', true)->sum('gross_profit') + $this->reserve_participant_airplane_prices->where('valid', true)->sum('gross_profit');
    }
}
