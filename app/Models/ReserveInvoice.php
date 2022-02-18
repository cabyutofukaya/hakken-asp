<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

/**
 * 通常請求モデル
 */
class ReserveInvoice extends Model implements ReserveDocumentInterface, DocumentPdfInterface
{
    use SoftDeletes,ModelLogTrait,SoftCascadeTrait,Sortable;

    // // 金額集計に使用
    // protected $with = [
    //     'agency_deposits',
    // ]; // 入金額

    protected $appends = [
        'sum_deposit',
        'sum_not_deposit',
    ];


    protected $softCascade = [
        'pdf',
    ];

    public $sortable = [
        'created_at',
        'reserve.control_number',
        'applicant_name',
        'billing_address_name',
        'amount_total',
        'deposit_amount',
        'not_deposit_amount',
        'issue_date',
        'payment_deadline',
        'departure_date',
        'last_manager.name',
        'last_note',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'business_user_id',
        'invoice_number',
        'user_invoice_number',
        'issue_date',
        'payment_deadline',
        'document_request_id',
        'document_common_id',
        'applicant_name',
        'billing_address_name',
        'document_address',
        'name',
        'departure_date',
        'return_date',
        'last_manager_id',
        'representative',
        'participant_ids',
        'document_setting',
        'document_common_setting',
        'option_prices',
        'airticket_prices',
        'hotel_prices',
        'hotel_info',
        'hotel_contacts',
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
    
    // テンプレート
    public function document_request()
    {
        return $this->belongsTo('App\Models\DocumentRequest')->withDefault();
    }

    // 共通設定
    public function document_common()
    {
        return $this->belongsTo('App\Models\DocumentCommon')->withDefault();
    }

    // 予約
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
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
    public function agency_deposits()
    {
        return $this->hasMany('App\Models\AgencyDeposit');
    }

    // 一括請求データ
    public function reserve_bundle_invoice()
    {
        return $this->belongsTo('App\Models\ReserveBundleInvoice')->withDefault();
    }
    
    // 領収書データ（請求書と一対一の関係）
    public function reserve_receipt()
    {
        return $this->hasOne('App\Models\ReserveReceipt')->withDefault();
    }

    /**
     * PDFファイル
     */
    public function pdf()
    {
        return $this->morphOne('App\Models\DocumentPdf', 'documentable');
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

    // 出発日（日付は「YYYY/MM/DD」形式に変換）
    public function getDepartureDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 帰着日（日付は「YYYY/MM/DD」形式に変換）
    public function getReturnDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 顧客種別が法人である
    public function getIsCorporateCustomerAttribute() : bool
    {
        return !is_null($this->business_user_id);
    }

    /**
     * 請求締日を取得（帰着日の月末を求める）
     * 一括請求用
     */
    public function getBundleInvoiceCutoffDateAttribute($value): ?string
    {
        if ($this->return_date) {
            return Carbon::create(date('Y', strtotime($this->return_date)), date('n', strtotime($this->return_date)), 1)->lastOfMonth();
        }
        return null;
    }

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.reserve_invoices.status');
        foreach (config("consts.reserve_invoices.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /////////////////


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
     * 代表者
     */
    public function getRepresentativeAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setRepresentativeAttribute($value)
    {
        $this->attributes['representative'] = $value ? json_encode($value) : null;
    }

    /**
     * 参加者ID
     */
    public function getParticipantIdsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setParticipantIdsAttribute($value)
    {
        $this->attributes['participant_ids'] = $value ? json_encode($value) : json_encode([]);
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
     * オプション科目
     */
    public function getOptionPricesAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setOptionPricesAttribute($value)
    {
        $this->attributes['option_prices'] = $value ? json_encode($value) : null;
    }

    /**
     * 航空券科目
     */
    public function getAirticketPricesAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setAirticketPricesAttribute($value)
    {
        $this->attributes['airticket_prices'] = $value ? json_encode($value) : null;
    }

    /**
     * ホテル科目
     */
    public function getHotelPricesAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setHotelPricesAttribute($value)
    {
        $this->attributes['hotel_prices'] = $value ? json_encode($value) : null;
    }

    /**
     * 宿泊施設情報
     */
    public function getHotelInfoAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setHotelInfoAttribute($value)
    {
        $this->attributes['hotel_info'] = $value ? json_encode($value) : null;
    }

    /**
     * 宿泊施設連絡先情報
     */
    public function getHotelContactsAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setHotelContactsAttribute($value)
    {
        $this->attributes['hotel_contacts'] = $value ? json_encode($value) : null;
    }

    ////////// 集計

    /**
     * 入金合計
     *
     */
    public function getSumDepositAttribute()
    {
        return $this->agency_deposits->sum('amount');
    }

    /**
     * 未入金合計
     */
    public function getSumNotDepositAttribute()
    {
        return $this->amount_total - $this->agency_deposits->sum('amount');
    }
}
