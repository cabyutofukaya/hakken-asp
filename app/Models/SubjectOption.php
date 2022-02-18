<?php

namespace App\Models;

use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use App\Traits\ChargeKbnTrait;
use Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * 科目マスター「オプション科目」
 */
class SubjectOption extends Model
{
    use Sortable, SoftDeletes, ModelLogTrait, HashidsTrait, ChargeKbnTrait;

    public $sortable = [
        'id',
        'kbn',
        'code',
        'name',
        // 'city.code',
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
        // 'city_id', とりあえず廃止
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

    // 都市・空港
    public function city()
    {
        return $this->belongsTo('App\Models\City')->withDefault();
    }

    // 仕入先ID
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault();
    }

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_subject_option_custom_values()
    {
        return $this->hasMany('App\Models\VSubjectOptionCustomValue')->where('flg', true);
    }

    /**
     * 区分を取得（有効な項目のみ対象 flg=1）
     */
    public function kbns()
    {
        return $this->hasMany('App\Models\VSubjectOptionCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_SUBJECT_OPTION_KBN'))
            ->where('flg', true);
    }

    /* ハッシュIDここから */

    // 都市・空港
    public function setCityIdAttribute($value)
    {
        $value = Hashids::decode($value)[0] ?? null;

        $this->attributes['city_id'] = $value;
    }

    /* ハッシュIDここまで */

   ///////////////// カスタムソート

    /**
     * 区分によるソートメソッド
     */
    public function kbnSortable($query, $direction)
    {
        return $query->select('subject_options.*')
            ->leftJoin('v_subject_option_custom_values', 'subject_options.id', '=', 'v_subject_option_custom_values.subject_option_id')
            ->where('v_subject_option_custom_values.code', config('consts.user_custom_items.CODE_SUBJECT_OPTION_KBN'))
            ->where('v_subject_option_custom_values.flg', true)
            ->orderBy('v_subject_option_custom_values.val', $direction);
    }

}
