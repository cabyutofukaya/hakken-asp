<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyWithdrawalItemHistory extends Model
{
    use ModelLogTrait,SoftDeletes;

    // 親テーブルとtimestamps連携
    protected $touches = ['account_payable_item'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'payment_type',
        'account_payable_item_id',
        'agency_withdrawal_id',
        'bulk_withdrawal_key',
        'amount',
        'withdrawal_date',
        'record_date',
        'manager_id',
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
        'agency_id',
        'account_payable_item_id',
        'updated_at',
        'deleted_at',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'integer',
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

    // 買掛金明細(商品毎)
    public function account_payable_item()
    {
        return $this->belongsTo('App\Models\AccountPayableItem')->withDefault();
    }

    // 予約
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withTrashed()->withDefault();
    }

    // 予約
    public function agency_withdrawal()
    {
        return $this->belongsTo('App\Models\AgencyWithdrawal')->withDefault();
    }

    /**
     * 自社担当
     * 論理削除も取得
     */
    public function manager()
    {
        return $this->belongsTo('App\Models\Staff', 'manager_id')
            ->withTrashed()
            ->withDefault();
    }

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     * agency_withdrawal_item_history用
     */
    public function v_agency_withdrawal_item_history_custom_values()
    {
        return $this->hasMany('App\Models\VAgencyWithdrawalItemHistoryCustomValue')->where('flg', true);
    }

    /**
     * 一括出金レコードの場合はtrue
     */
    public function is_bulk_withdrawal(): bool
    {
        return $this->payment_type == config('consts.agency_withdrawal_item_histories.PAYMENT_TYPE_BULK');
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    // 出金日（日付は「YYYY/MM/DD」形式に変換）
    public function getWithdrawalDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 登録日（日付は「YYYY/MM/DD」形式に変換）
    public function getRecordDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }
}
