<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 一括請求モデル
 */
class ReserveBundleInvoice extends Model implements DocumentPdfInterface
{
    use SoftDeletes,ModelLogTrait;

    protected $appends = [
        'sum_deposit',
        'sum_not_deposit',
    ];

    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'business_user_id',
        'bundle_invoice_number',
        'user_bundle_invoice_number',
        'cutoff_date',
        'issue_date',
        'payment_deadline',
        'period_from',
        'period_to',
        'document_request_all_id',
        'document_common_id',
        'billing_address_name',
        'document_address',
        'name',
        'last_manager_id',
        'partner_manager_ids',
        'document_setting',
        'document_common_setting',
        'reserve_prices',
        'reserve_cancel_info',
        'amount_total',
        'deposit_amount',
        'not_deposit_amount',
        'status',
        'last_note',
    ];

    protected $casts = [
        'amount_total' => 'integer',
        'deposit_amount' => 'integer',
        'not_deposit_amount' => 'integer',
    ];

    protected $softCascade = [
        'pdf',
    ];

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }
    
    // 請求書
    public function reserve_invoices()
    {
        return $this->hasMany('App\Models\ReserveInvoice');
    }

    // 法人顧客
    public function business_user()
    {
        return $this->belongsTo('App\Models\BusinessUser')->withDefault();
    }

    // (最後の)担当者
    public function last_manager()
    {
        return $this->belongsTo('App\Models\Staff')->withDefault();
    }

    // 入金管理
    public function agency_bundle_deposits()
    {
        return $this->hasMany('App\Models\AgencyBundleDeposit');
    }

    /**
     * PDFファイル
     */
    public function pdf()
    {
        return $this->morphOne('App\Models\DocumentPdf', 'documentable');
    }

    ///////////////// jsonエンコード・デコード ここから /////////////

    /**
     * 宛名情報
     */
    public function getDocumentAddressAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setDocumentAddressAttribute($value)
    {
        $this->attributes['document_address'] = $value ? json_encode($value) : null;
    }

    /**
     * 担当者ID
     */
    public function getPartnerManagerIdsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setPartnerManagerIdsAttribute($value)
    {
        $this->attributes['partner_manager_ids'] = $value ? json_encode($value) : json_encode([]);
    }

    /**
     * 書類設定
     */
    public function getDocumentSettingAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setDocumentSettingAttribute($value)
    {
        $this->attributes['document_setting'] = $value ? json_encode($value) : null;
    }

    /**
     * 共通設定情報
     */
    public function getDocumentCommonSettingAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setDocumentCommonSettingAttribute($value)
    {
        $this->attributes['document_common_setting'] = $value ? json_encode($value) : null;
    }

    /**
     * 料金詳細
     */
    public function getReservePricesAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setReservePricesAttribute($value)
    {
        $this->attributes['reserve_prices'] = $value ? json_encode($value) : null;
    }

    /**
     * キャンセル予約情報
     */
    public function getReserveCancelInfoAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setReserveCancelInfoAttribute($value)
    {
        $this->attributes['reserve_cancel_info'] = $value ? json_encode($value) : null;
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    // 発行日（日付は「YYYY/MM/DD」形式に変換）
    public function getIssueDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 支払い期限（日付は「YYYY/MM/DD」形式に変換）
    public function getPaymentDeadlineAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 期間開始（日付は「YYYY/MM/DD」形式に変換）
    public function getPeriodFromAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 期間終了（日付は「YYYY/MM/DD」形式に変換）
    public function getPeriodToAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    ////////// 集計

    /**
     * 入金合計
     * 
     */
    public function getSumDepositAttribute()
    {
        return $this->agency_bundle_deposits->sum('amount');
    }

    /**
     * 未入金合計
     */
    public function getSumNotDepositAttribute()
    {
        return $this->amount_total - $this->agency_bundle_deposits->sum('amount');
    }
}
