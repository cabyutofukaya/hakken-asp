<?php

namespace App\Models;

use Lang;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

/**
 * ユーザー用カスタム項目管理テーブル
 */
class UserCustomItem extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = [
        'agency_bundle_deposit_custom_value',
        'agency_consultation_custom_value',
        'agency_deposit_custom_value',
        'agency_withdrawal_custom_value',
        'business_user_custom_value',
        'reserve_custom_value',
        'reserve_purchasing_subject_airplane_custom_value',
        'reserve_purchasing_subject_hotel_custom_value',
        'reserve_purchasing_subject_option_custom_value',
        'staff_custom_value',
        'subject_airplane_custom_value',
        'subject_hotel_custom_value',
        'subject_option_custom_value',
        'supplier_custom_value',
        'user_custom_value',
        'user_mileage_custom_value',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'user_custom_category_id',
        'user_custom_category_item_id',
        'agency_id',
        'type',
        'display_position',
        'name',
        'code',
        'fixed_item',
        'undelete_item',
        'unedit_item',
        'input_type',
        'list',
        'protect_list',
        'flg',
        'seq',
    ];

    protected $casts = [
        'undelete_item' => 'boolean',
        'unedit_item' => 'boolean',
        'fixed_item' => 'boolean',
        'flg' => 'boolean',
    ];

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency');
    }

    // カテゴリ項目定義テーブル
    public function user_custom_category_item()
    {
        return $this->belongsTo('App\Models\UserCustomCategoryItem')->withDefault();
    }

    // カテゴリ定義テーブル
    public function user_custom_category()
    {
        return $this->belongsTo('App\Models\UserCustomCategory')->withDefault();
    }

    // スタッフ値データ
    public function staff_custom_value()
    {
        return $this->hasMany('App\Models\StaffCustomValue');
    }

    // 仕入先値データ
    public function supplier_custom_value()
    {
        return $this->hasMany('App\Models\SupplierCustomValue');
    }

    // オプション科目値データ
    public function subject_option_custom_value()
    {
        return $this->hasMany('App\Models\SubjectOptionCustomValue');
    }

    // 航空券科目科目値データ
    public function subject_airplane_custom_value()
    {
        return $this->hasMany('App\Models\SubjectAirplaneCustomValue');
    }

    // ホテル科目科目値データ
    public function subject_hotel_custom_value()
    {
        return $this->hasMany('App\Models\SubjectHotelCustomValue');
    }

    // 個人顧客値データ
    public function user_custom_value()
    {
        return $this->hasMany('App\Models\UserCustomValue');
    }

    // 法人顧客値データ
    public function business_user_custom_value()
    {
        return $this->hasMany('App\Models\BusinessUserCustomValue');
    }

    // 見積・予約値データ
    public function reserve_custom_value()
    {
        return $this->hasMany('App\Models\ReserveCustomValue');
    }

    public function agency_bundle_deposit_custom_value()
    {
        return $this->hasMany('App\Models\AgencyBundleDepositCustomValue');
    }

    public function agency_deposit_custom_value()
    {
        return $this->hasMany('App\Models\AgencyDepositCustomValue');
    }

    // 相談
    public function agency_consultation_custom_value()
    {
        return $this->hasMany('App\Models\AgencyConsultationCustomValue');
    }

    public function agency_withdrawal_custom_value()
    {
        return $this->hasMany('App\Models\AgencyWithdrawalCustomValue');
    }

    public function reserve_purchasing_subject_airplane_custom_value()
    {
        return $this->hasMany('App\Models\ReservePurchasingSubjectAirplaneCustomValue');
    }

    public function reserve_purchasing_subject_hotel_custom_value()
    {
        return $this->hasMany('App\Models\ReservePurchasingSubjectHotelCustomValue');
    }

    public function reserve_purchasing_subject_option_custom_value()
    {
        return $this->hasMany('App\Models\ReservePurchasingSubjectOptionCustomValue');
    }

    public function user_mileage_custom_value()
    {
        return $this->hasMany('App\Models\UserMileageCustomValue');
    }
    

    // 表示位置名称を取得
    public function getDisplayPositionNameAttribute(): ?string
    {
        $values = Lang::get('values.user_custom_items.position');
        foreach (config("consts.user_custom_items.POSITION_LIST") as $key => $val) {
            if ($this->display_position == $val && Arr::get($values, $key)) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    // プレフィックス + key
    public function setKeyAttribute($value)
    {
        // 入力タイプに応じて接頭辞を切り替え
        $prefix = '';
        switch ($this->input_type) {
            case config('consts.user_custom_items.INPUT_TYPE_TEXT_01'): // 一行テキスト
                $prefix = config('consts.user_custom_items.USER_CUSTOM_ITEM_ONELINE_PREFIX');
                break;
            case config('consts.user_custom_items.INPUT_TYPE_TEXT_02'): // 複数行テキスト
                $prefix = config('consts.user_custom_items.USER_CUSTOM_ITEM_MULTIPLE_PREFIX');
                break;
            case config('consts.user_custom_items.INPUT_TYPE_DATE_01'): // カレンダー
                $prefix = config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX');
                break;
            case config('consts.user_custom_items.INPUT_TYPE_DATE_02'): // 時刻
                $prefix = config('consts.user_custom_items.USER_CUSTOM_ITEM_TIME_PREFIX');
                break;
            default: // 一般テキスト（現状はリストタイプのみ）
                $prefix = config('consts.user_custom_items.USER_CUSTOM_ITEM_TEXT_PREFIX');
        }
        $this->attributes['key'] = $prefix . $value;
    }

    /**
     * listデータを 値=>値 形式の配列で取得
     */
    public function select_item($default): array
    {
        if ($this->list) {
            // array_mergeだと$this->listに数字があった場合配列のキーがおかしくなってしまったので＋結合に変更
            return $default + array_combine($this->list, $this->list);
            // return array_merge($default, array_combine($this->list, $this->list));
        }
        return $default;
    }

    // リストアイテムを取得(値のみの配列)
    public function getListAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    // リストアイテムをセット
    public function setListAttribute($value)
    {
        $value = array_filter($value, "strlen"); // item配列から空要素を除去
        $this->attributes['list'] = json_encode($value);
    }

    // 保護リストアイテムを取得(値のみの配列)
    public function getProtectListAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    // 保護リストアイテムをセット
    public function setProtectListAttribute($value)
    {
        $value = array_filter($value, "strlen"); // item配列から空要素を除去
        $this->attributes['protect_list'] = json_encode($value);
    }
}
