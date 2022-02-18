<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;


class ReserveConfirm extends Model implements ReserveDocumentInterface, DocumentPdfInterface
{
    use SoftDeletes,ModelLogTrait,Sortable,SoftCascadeTrait;

    protected $softCascade = [
        'pdf',
    ];

    public $sortable = [
        'control_number',
        'confirm_number',
        'created_at',
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
        'control_number',
        'confirm_number',
        'issue_date',
        'document_quote_id',
        'document_common_id',
        'document_address',
        'name',
        'departure_date',
        'return_date',
        'manager',
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
        'status',
    ];

    protected $guarded = [
        'confirm_number',
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
        'amount_total' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
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

    // 行程管理
    public function reserve_itinerary()
    {
        return $this->belongsTo('App\Models\ReserveItinerary')->withDefault();
    }

    // テンプレート
    public function document_quote()
    {
        return $this->belongsTo('App\Models\DocumentQuote')->withDefault();
    }

    // 共通設定
    public function document_common()
    {
        return $this->belongsTo('App\Models\DocumentCommon')->withDefault();
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
}
