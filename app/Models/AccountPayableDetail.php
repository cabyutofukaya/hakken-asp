<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

/**
 * 仕入れ先買掛金明細管理
 */
class AccountPayableDetail extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable;

    // protected $with = [
    //     'reserve',
    // ]; // 仕入額の算定に予約状態を考慮するため、withしておく

    public $sortable = [
        'id',
        'reserve.control_number',
        'supplier_name',
        'item_id',
        'item_code',
        'item_name',
        'subject',
        'last_manager.name',
        'last_note',
        'payment_date',
        'use_date',
        'unpaid_balance',
        'amount_billed',
        'status',
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
        'reserve_travel_date_id',
        'reserve_schedule_id',
        // 'account_payable_id',
        'supplier_id',
        'supplier_name',
        'item_name',
        'item_code',
        'use_date',
        'payment_date',
        'saleable_type',
        'saleable_id',
        'amount_billed',
        'amount_payment',
        'unpaid_balance',
        // 'official', // ←使っていないかも
        'last_manager_id',
        'status',
        'last_note',
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
        'amount_billed' => 'integer',
        'amount_payment' => 'integer',
        'unpaid_balance' => 'integer',
    ];

    protected $dates = [
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    /**
     * 料金
     */
    public function saleable()
    {
        return $this->morphTo();
    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 予約
    // TODO 削除済みも含めて取得するように修正したので様子見
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withTrashed()->withDefault();
    }
    
    // // 仕入先ごとにまとめた買掛金親レコード
    // public function account_payable()
    // {
    //     return $this->belongsTo('App\Models\AccountPayable')->withDefault();
    // }

    // 仕入先
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault();
    }

    // 出金管理
    public function agency_withdrawals()
    {
        return $this->hasMany('App\Models\AgencyWithdrawal');
    }

    // (最後の)担当者
    public function last_manager()
    {
        return $this->belongsTo('App\Models\Staff')->withDefault();
    }
    
    //////////////////// ローカルスコープ ここから /////////////////////////

    /**
     * 決定項目（予約ステータスの予約レコードと紐づいたレコード）
     *
     * @param $query
     * @return mixed
     */
    public function scopeDecided($query)
    {
        return $query->whereHas('reserve', function ($q) {
            $q->where('application_step', config('consts.reserves.APPLICATION_STEP_RESERVE'));
        });
    }

    /**
     * 0円を除く
     *
     * @param $query
     * @return mixed
     */
    public function scopeExcludingzero($query)
    {
        return $query->where(function ($q) {
            $q->where('amount_billed', "<>", 0)
                ->orWhere('unpaid_balance', "<>", 0);
        })->where('status', '<>', config('consts.account_payable_details.STATUS_NONE'));
    }

    // /**
    //  * 有効にチェックが入った仕入項目のみ対象
    //  */
    // public function scopeIsValid($query)
    // {
    //     return $query->whereHasMorph('saleable', [
    //         'App\Models\ReserveParticipantOptionPrice', 
    //         'App\Models\ReserveParticipantAirplanePrice', 
    //         'App\Models\ReserveParticipantHotelPrice',
    //     ], function ($q) {
    //         $q->where('valid', true);
    //     });
    // }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    // 支払日（日付は「YYYY/MM/DD」形式に変換）
    public function getPaymentDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 利用日（日付は「YYYY/MM/DD」形式に変換）
    public function getUseDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.account_payable_details.status');
        foreach (config("consts.account_payable_details.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }
}
