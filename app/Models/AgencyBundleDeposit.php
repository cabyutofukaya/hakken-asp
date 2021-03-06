<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class AgencyBundleDeposit extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable;

    // 親テーブルとtimestamps連携
    protected $touches = ['reserve_bundle_invoice'];

    public $sortable = [
        'id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier_id',
        'agency_id',
        'reserve_bundle_invoice_id',
        'amount',
        'deposit_date',
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
        'reserve_bundle_invoice_id',
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

    // 請求管理
    public function reserve_bundle_invoice()
    {
        return $this->belongsTo('App\Models\ReserveBundleInvoice')->withDefault();
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
     */
    public function v_agency_bundle_deposit_custom_values()
    {
        return $this->hasMany('App\Models\VAgencyBundleDepositCustomValue')->where('flg', true);
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    // 入金日（日付は「YYYY/MM/DD」形式に変換）
    public function getDepositDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 登録日（日付は「YYYY/MM/DD」形式に変換）
    public function getRecordDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

}
