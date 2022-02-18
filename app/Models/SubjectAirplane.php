<?php

namespace App\Models;

use App\Traits\ChargeKbnTrait;
use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * 科目マスター「航空券科目」
 */
class SubjectAirplane extends Model
{
    use Sortable, SoftDeletes, ModelLogTrait, HashidsTrait, ChargeKbnTrait;

    public $sortable = [
        'id',
        'code',
        'name',
        'airline',
        'booking_class',
        'departure.name',
        'destination.name',
        'ad_gross',
        'ad_net',
        'supplier.name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'subject_category_id',
        'code',
        'name',
        'booking_class',
        'departure_id',
        'destination_id',
        'supplier_id',
        'ad_gross_ex',
        'ad_zei_kbn',
        'ad_gross',
        'ad_cost',
        'ad_commission_rate',
        'ad_net',
        'ad_gross_profit',
        'ch_gross_ex',
        'ch_zei_kbn',
        'ch_gross',
        'ch_cost',
        'ch_commission_rate',
        'ch_net',
        'ch_gross_profit',
        'inf_gross_ex',
        'inf_zei_kbn',
        'inf_gross',
        'inf_cost',
        'inf_commission_rate',
        'inf_net',
        'inf_gross_profit',
        'note',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ad_gross_ex' => 'integer',
        'ad_gross' => 'integer',
        'ad_cost' => 'integer',
        'ad_commission_rate' => 'integer',
        'ad_net' => 'integer',
        'ad_gross_profit' => 'integer',
        'ch_gross_ex' => 'integer',
        'ch_gross' => 'integer',
        'ch_cost' => 'integer',
        'ch_commission_rate' => 'integer',
        'ch_net' => 'integer',
        'ch_gross_profit' => 'integer',
        'inf_gross_ex' => 'integer',
        'inf_gross' => 'integer',
        'inf_cost' => 'integer',
        'inf_commission_rate' => 'integer',
        'inf_net' => 'integer',
        'inf_gross_profit' => 'integer',
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

    // 科目カテゴリ
    public function subject_category()
    {
        return $this->belongsTo('App\Models\SubjectCategory')->withDefault();
    }

    // 出発地
    public function departure()
    {
        return $this->belongsTo('App\Models\City', 'departure_id')->withDefault();
    }

    // 目的地
    public function destination()
    {
        return $this->belongsTo('App\Models\City', 'destination_id')->withDefault();
    }

    // 仕入先ID
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault();
    }

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_subject_airplane_custom_values()
    {
        return $this->hasMany('App\Models\VSubjectAirplaneCustomValue')->where('flg', true);
    }

    /**
     * 航空会社を取得（有効な項目のみ対象 flg=1）
     */
    public function airlines()
    {
        return $this->hasMany('App\Models\VSubjectAirplaneCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY'))
            ->where('flg', true);
    }

    /* ハッシュIDここから */

    // // 出発地
    // public function setDepartureIdAttribute($value)
    // {
    //     $value = Hashids::decode($value)[0] ?? null;

    //     $this->attributes['departure_id'] = $value;
    // }

    // // 目的地
    // public function setDestinationIdAttribute($value)
    // {
    //     $value = Hashids::decode($value)[0] ?? null;

    //     $this->attributes['destination_id'] = $value;
    // }

    // // 仕入先
    // public function setSupplierIdAttribute($value)
    // {
    //     $value = Hashids::decode($value)[0] ?? null;

    //     $this->attributes['supplier_id'] = $value;
    // }

    /* ハッシュIDここまで */

    ///////////////// カスタムソート

    /**
     * 区分によるソートメソッド
     */
    public function airlineSortable($query, $direction)
    {
        return $query->select('subject_airplanes.*')
            ->leftJoin('v_subject_airplane_custom_values', 'subject_airplanes.id', '=', 'v_subject_airplane_custom_values.subject_airplane_id')
            ->where('v_subject_airplane_custom_values.code', config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY'))
            ->where('v_subject_airplane_custom_values.flg', true)
            ->orderBy('v_subject_airplane_custom_values.val', $direction);
    }

}
