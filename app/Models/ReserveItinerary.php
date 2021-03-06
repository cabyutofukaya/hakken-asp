<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use App\Traits\HashidsTrait;


class ReserveItinerary extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable,SoftCascadeTrait,HashidsTrait;

    // protected $touches = ['reserve'];

    // // 料金レコードの集計に使用
    // protected $with = [
    //     'reserve_participant_option_prices',
    //     'reserve_participant_hotel_prices', 
    //     'reserve_participant_airplane_prices',
    // ]; // 参加者料金（オプション科目、ホテル科目、航空券科目）

    protected $appends = [
        // 'sum_gross',
        // 'sum_cancel_gross',
        // 'sum_net',
        // 'sum_cancel_net',
        // 'sum_gross_profit',
        // 'sum_cancel_charge_profit',
    ];

    protected $softCascade = [
        'reserve_travel_dates',
        'reserve_confirms',
        'account_payables',
        'account_payable_items',
        'account_payable_details',
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
        'total_gross',
        'total_net',
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
        'total_gross' => 'integer',
        'total_net' => 'integer',
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

    // 仕入れ先買掛金(詳細)
    public function account_payable_details()
    {
        return $this->hasMany('App\Models\AccountPayableDetail');
    }

    // 仕入れ先買掛金(仕入先＆商品毎)
    public function account_payable_items()
    {
        return $this->hasMany('App\Models\AccountPayableItem');
    }

    /**
     * 帳票登録数を取得
     */
    public function getReserveConfirmNumAttribute() : int
    {
        return $this->reserve_confirms->count();
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////

    // ハッシュID
    public function getHashIdAttribute($value) : string
    {
        return $this->getRouteKey();
    }

    ///////////////  集計メソッド ここから ///////////////

    /**
     * GROSS合計（オプション科目+航空券科目+ホテル科目）
     * 通常仕入の場合はvalid=true
     * キャンセル仕入の場合はis_cancel=trueのみ集計
     */
    public function getSumGrossAttribute()
    {
        return 
            $this->reserve_participant_option_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('gross') + 
            $this->reserve_participant_option_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge') + 
            $this->reserve_participant_hotel_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('gross') + 
            $this->reserve_participant_hotel_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge') + 
            $this->reserve_participant_airplane_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('gross') + 
            $this->reserve_participant_airplane_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge');
    }

    // /**
    //  * キャンセルチャージGROSS合計（オプション科目+航空券科目+ホテル科目）
    //  * is_cancel=trueのキャセルチャージの合計
    //  */
    // public function getSumCancelGrossAttribute()
    // {
    //     return $this->reserve_participant_option_prices->where('is_cancel', true)->sum('cancel_charge') + $this->reserve_participant_hotel_prices->where('is_cancel', true)->sum('cancel_charge') + $this->reserve_participant_airplane_prices->where('is_cancel', true)->sum('cancel_charge');
    // }

    /**
     * NET合計（オプション科目+航空券科目+ホテル科目）
     * 通常仕入の場合はvalid=true
     * キャンセル仕入の場合はis_cancel=trueのみ集計
     */
    public function getSumNetAttribute()
    {
        return 
            $this->reserve_participant_option_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('net') + 
            $this->reserve_participant_option_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge_net') + 
            $this->reserve_participant_hotel_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('net') + 
            $this->reserve_participant_hotel_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge_net') + 
            $this->reserve_participant_airplane_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('net') + 
            $this->reserve_participant_airplane_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge_net');
    }

    // /**
    //  * キャンセルチャージNET合計（オプション科目+航空券科目+ホテル科目）
    //  * is_cancel=trueのキャセルチャージの合計
    //  */
    // public function getSumCancelNetAttribute()
    // {
    //     return $this->reserve_participant_option_prices->where('is_cancel', true)->sum('cancel_charge_net') + $this->reserve_participant_hotel_prices->where('is_cancel', true)->sum('cancel_charge_net') + $this->reserve_participant_airplane_prices->where('is_cancel', true)->sum('cancel_charge_net');
    // }

    /**
     * 粗利合計（オプション科目+航空券科目+ホテル科目）
     * 通常仕入の場合はvalid=true
     * キャンセル仕入の場合はis_cancel=trueのみ集計
     */
    public function getSumGrossProfitAttribute()
    {
        return 
            $this->reserve_participant_option_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('gross_profit') + 
            $this->reserve_participant_option_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge_profit') + 
            $this->reserve_participant_hotel_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('gross_profit') + 
            $this->reserve_participant_hotel_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge_profit') + 
            $this->reserve_participant_airplane_prices
                ->where('purchase_type', config('consts.const.PURCHASE_NORMAL'))
                ->where('valid', true)
                ->sum('gross_profit') + 
            $this->reserve_participant_airplane_prices
                ->where('purchase_type', config('consts.const.PURCHASE_CANCEL'))
                ->where('is_cancel', true)
                ->sum('cancel_charge_profit');
    }

    // /**
    //  * キャンセルチャージ粗利合計（オプション科目+航空券科目+ホテル科目）
    //  * is_cancel=trueのキャセルチャージの合計
    //  */
    // public function getSumCancelChargeProfitAttribute()
    // {
    //     return $this->reserve_participant_option_prices->where('is_cancel', true)->sum('cancel_charge_profit') + $this->reserve_participant_hotel_prices->where('is_cancel', true)->sum('cancel_charge_profit') + $this->reserve_participant_airplane_prices->where('is_cancel', true)->sum('cancel_charge_profit');
    // }
}
