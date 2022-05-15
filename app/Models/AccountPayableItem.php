<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

/**
 * 仕入れ先買掛金(仕入先＆商品毎)管理
 */
class AccountPayableItem extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable;

    public $sortable = [
        'item_code',
        'item_name',
        'status',
        'supplier_name',
        'amount_billed',
        'unpaid_balance',
        'last_manager.name',
        'last_note',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payable_number',
        'reserve_itinerary_id',
        'supplier_id',
        'supplier_name',
        'item_id',
        'item_code',
        'item_name',
        'subject',
        'agency_id',
        'reserve_id',
        'amount_billed',
        'unpaid_balance',
        'payment_date',
        'last_manager_id',
        'last_note',
        'status',
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
        'unpaid_balance' => 'integer',
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
        return $this->belongsTo('App\Models\Reserve')->withTrashed()->withDefault();
    }

    // 仕入先
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault();
    }

    // 出金管理
    public function agency_withdrawal_item_histories()
    {
        return $this->hasMany('App\Models\AgencyWithdrawalItemHistory');
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

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.account_payable_items.status');
        foreach (config("consts.account_payable_items.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }
}
