<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Kyslik\ColumnSortable\Sortable;

/**
 * 仕入れ先買掛金管理
 */
class AccountPayable extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable;

    // // 集計に使用
    // protected $with = [
    //     'account_payable_details',
    //     'agency_withdrawals',
    // ];
    
    protected $appends = [
        'sum_net', // 無効仕入含む
        'sum_enabled_net', // 無効仕入除く(valid=1)
        'sum_withdrawal',
    ];


    public $sortable = [
        'created_at',
    ];

    protected $softCascade = [
        'account_payable_details',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'reserve_itinerary_id',
        'payable_number',
        'supplier_id',
        'supplier_name',
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
    ];

    protected $dates = [
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

    // 行程
    public function reserve_itinerary()
    {
        return $this->belongsTo('App\Models\ReserveItinerary')->withDefault();
    }

    // 仕入先
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault();
    }

    // 明細(無効仕入も含む全明細)
    public function account_payable_details()
    {
        return $this->hasMany('App\Models\AccountPayableDetail');
    }

    // 有効な買い掛け金詳細
    public function enabled_account_payable_details()
    {
        // 仕入が有効になっているレコードのみ対象に(AccountPayableDetailのscopeIsValidメソッドと同じ処理)
        return $this->hasMany('App\Models\AccountPayableDetail')->whereHasMorph('saleable', [
            'App\Models\ReserveParticipantOptionPrice', 
            'App\Models\ReserveParticipantAirplanePrice', 
            'App\Models\ReserveParticipantHotelPrice',
        ], function ($q) {
            $q->where('valid', true);
        });
    }

    /**
     * 当レコードに紐付く出金データ
     */
    public function agency_withdrawals()
    {
        return $this->hasManyThrough('App\Models\AgencyWithdrawal', 'App\Models\AccountPayableDetail');

    }

    ///////////////  集計メソッド ここから ///////////////

    /**
     * NET合計(無効仕入含む)
     */
    public function getSumNetAttribute()
    {
        return $this->account_payable_details->sum('amount_billed');
    }

    /**
     * NET合計(無効仕入除く)
     */
    public function getSumEnabledNetAttribute()
    {
        return $this->enabled_account_payable_details->sum('amount_billed');
    }

    /**
     * 出金合計
     */
    public function getSumWithdrawalAttribute()
    {
        return $this->agency_withdrawals->sum('amount');
    }
}
