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
 * 領収書データ
 */
class ReserveReceipt extends Model implements DocumentPdfInterface
{
    use SoftDeletes,ModelLogTrait,SoftCascadeTrait;

    protected $touches = ['reserve']; // 書類を更新した場合は念の為予約情報も更新

    // // 金額集計に使用
    // protected $with = [
    //     'agency_deposits',
    // ]; // 入金額

    // protected $appends = [
    //     'sum_deposit',
    //     'sum_not_deposit',
    // ];


    protected $softCascade = [
        'pdf',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'reserve_invoice_id',
        'receipt_number',
        'user_receipt_number',
        'issue_date',
        'document_receipt_id',
        'document_common_id',
        'document_address',
        'document_setting',
        'document_common_setting',
        'receipt_amount',
        'status',
    ];

    protected $casts = [
        'receipt_amount' => 'integer',
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

    // 予約情報
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
    }

    // テンプレート
    public function document_receipt()
    {
        return $this->belongsTo('App\Models\DocumentReceipt')->withDefault();
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

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.reserve_receipts.status');
        foreach (config("consts.reserve_receipts.STATUS_LIST") as $key => $val) {
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
}

