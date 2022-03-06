<?php

namespace App\Models;

use App\Traits\VCustomValueTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;
use Vinkla\Hashids\Facades\Hashids;

/**
 * reserve_bundle_invoicesとreserve_invoicesテーブルを
 * 合体したviewモデル 
 */
class VReserveInvoice extends Model
{
    use Sortable, VCustomValueTrait;

    /**
     * プライマリーキー無効 
     */
    protected $primaryKey = null;

    /**
     * AutoIncrement無効
     */
    public $incrementing = false;

    //　集計用
    protected $appends = [
        'sum_deposit', // 通常請求
        'sum_not_deposit', // 通常請求
        'sum_bundle_deposit', // 一括請求
        'sum_not_bundle_deposit', // 一括請求
    ];
    
    public $sortable = [
        'created_at',
        'reserve.control_number',
        'applicant_name',
        'billing_address_name',
        'is_pay_altogether',
        'amount_total',
        'deposit_amount',
        'not_deposit_amount',
        'issue_date',
        'payment_deadline',
        'departure_date',
        'last_manager.name',
        'last_note',
    ];

    protected $casts = [
        'amount_total' => 'integer',
        'deposit_amount' => 'integer',
        'not_deposit_amount' => 'integer',
    ];

    /**
     * 予約情報
     */
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault()->withTrashed(); // 削除済みも取得
    }

    // (最後の)担当者
    public function last_manager()
    {
        return $this->belongsTo('App\Models\Staff')->withDefault();
    }    

    // 通常請求の入金管理
    public function agency_deposits()
    {
        return $this->hasManyThrough(
            'App\Models\AgencyDeposit', 
            'App\Models\ReserveInvoice', 
            'id',
            'reserve_invoice_id',
            'reserve_invoice_id',
            'id'
        );
    }

    // 一括請求の入金管理
    public function agency_bundle_deposits()
    {
        return $this->hasManyThrough(
            'App\Models\AgencyBundleDeposit', 
            'App\Models\ReserveBundleInvoice', 
            'id',
            'reserve_bundle_invoice_id',
            'reserve_bundle_invoice_id',
            'id'
        );
    }


    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////

    // 発行日（日付は「YYYY/MM/DD」形式に変換）
    public function getIssueDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 支払期限（日付は「YYYY/MM/DD」形式に変換）
    public function getPaymentDeadlineAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 出発日（日付は「YYYY/MM/DD」形式に変換）
    public function getDepartureDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    /**
     * reserve_bundle_invoice_idをハッシュ化して取得
     */
    public function getReserveBundleInvoiceHashIdAttribute(): ?string
    {
        return $this->reserve_bundle_invoice_id ? Hashids::encode($this->reserve_bundle_invoice_id) : null; 
    }

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.v_reserve_invoices.status');
        foreach (config("consts.v_reserve_invoices.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    ////////// 集計

    /**
     * 入金合計(通常請求)
     * 
     */
    public function getSumDepositAttribute()
    {
        return $this->agency_deposits->sum('amount');
    }

    /**
     * 未入金合計(通常請求)
     */
    public function getSumNotDepositAttribute()
    {
        return $this->amount_total - $this->agency_deposits->sum('amount');
    }

    /**
     * 入金合計(一括通常請求)
     * 
     */
    public function getSumBundleDepositAttribute()
    {
        return $this->agency_bundle_deposits->sum('amount');
    }

    /**
     * 未入金合計(一括通常請求)
     */
    public function getSumNotBundleDepositAttribute()
    {
        return $this->amount_total - $this->agency_bundle_deposits->sum('amount');
    }
}
