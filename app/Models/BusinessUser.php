<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

class BusinessUser extends Model
{
    use SoftDeletes,Sortable,ModelLogTrait,SoftCascadeTrait;

    protected $softCascade = [
        'agency_consultations', // 当該会社を消したら相談履歴も削除する
        'business_user_managers'
    ];

    // TODO 顧客区分を入れる
    public $sortable = [
        'id',
        'user_number',
        'name',
        'tel',
        'kbn',
        'prefecture_code',
        'address',
        'status'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_number',
        'agency_id',
        'name',
        'name_kana',
        'name_roman',
        'tel',
        'fax',
        'zip_code',
        'prefecture_code',
        'address1',
        'address2',
        'manager_id',
        'pay_altogether',
        'note',
        'status',
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
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
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

    // 都道府県
    public function prefecture()
    {
        return $this->belongsTo('App\Models\Prefecture', 'prefecture_code', 'code')->withDefault();
    }

    /**
     * 取引先担当者
     */
    public function business_user_managers()
    {
        return $this->hasMany('App\Models\BusinessUserManager');
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
    
    // 相談
    public function agency_consultations()
    {
        return $this->hasMany('App\Models\AgencyConsultation');
    }
    
    /**
     * 一括請求書一覧
     */
    public function reserve_bundle_invoices()
    {
        return $this->hasMany('App\Models\ReserveBundleInvoices');
    }

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_business_user_custom_values()
    {
        return $this->hasMany('App\Models\VBusinessUserCustomValue')->where('flg', true);
    }

    /**
     * 顧客区分（カスタム項目）
     *
     * 有効な項目のみ対象 flg=1
     * hasManyなので実際に値を取る場合は0番目の配列を使用する
     */
    public function kbns()
    {
        return $this->hasMany('App\Models\VBusinessUserCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_BUSINESS_CUSTOMER_KBN'))
            ->where('flg', true);
    }

    /**
     * 申し込んだ全予約の取得
     */
    public function reserves()
    {
        return $this->morphMany('App\Models\Reserve', 'applicantable');
    }

    // // 申込者種別名を取得
    // public function getApplicantTypeAttribute(): string
    // {
    //     return config('consts.reserves.PARTICIPANT_TYPE_BUSINESS');
    // }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////

    /**
     * 法人顧客名
     *
     * ステータスが有効ではないユーザーを表示する際は末尾に (XX) を明記
     * 削除ユーザーは（削除）を表記
     */
    public function getNameAttribute($value): ?string
    {
        if ($this->trashed()) {
            return sprintf("%s(削除)", $value);
        } else {
            if ($this->status != config('consts.business_users.STATUS_VALID')) {
                $statuses = get_const_item('business_users', 'status');
                return sprintf("%s(%s)", $value, Arr::get($statuses, $this->status));
            }
        }
        return $value;
    }

    /**
     * 一括支払契約値を文字に変換
     */
    public function getPayAltogetherLabelAttribute(): ?string
    {
        $values = Lang::get('values.business_users.pay_altogether');
        foreach (config("consts.business_users.PAY_ALTOGETHER_LIST") as $key => $val) {
            if ($val == $this->pay_altogether) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 郵便番号を「3桁-4桁」形式に変換
     */
    public function getZipCodeLabelAttribute(): ?string
    {
        return $this->zip_code ? sprintf("%s-%s", substr($this->zip_code, 0, 3), substr($this->zip_code, 3)) : null;
    }

    /**
     * 住所文字列をワンライナーで
     */
    public function getAddressLabelAttribute(): ?string
    {
        $address = sprintf("%s%s%s", $this->prefecture->name, $this->address1, $this->address2);
        return $address ? $address : null;
    }

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.business_users.status');
        foreach (config("consts.business_users.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }
    
    ///////////////// 読みやすい文字列に変換するAttribute ここまで //////////////


    ///////////////// カスタムソート

    /**
     * 住所1と住所2によるソートメソッド
     */
    public function addressSortable($query, $direction)
    {
        return $query->orderBy('address1', $direction)->orderBy('address2', $direction);
    }

    // /**
    //  * 顧客区分によるソートメソッド
    //  */
    // public function customerKbnSortable($query, $direction)
    // {
    //     return $query->select('business_users.*')
    //         ->leftJoin('v_business_user_custom_values', 'business_users.id', '=', 'v_business_user_custom_values.business_user_id')
    //         ->where('v_business_user_custom_values.code', config('consts.user_custom_items.CODE_BUSINESS_CUSTOMER_KBN'))
    //         ->where('v_business_user_custom_values.flg', true)
    //         ->orderBy('v_business_user_custom_values.val', $direction);
    // }

    /**
     * TODO distinctをつけるとソートできなくなるので再考
     * 
     * 顧客区分によるソートメソッド
     */
    public function kbnSortable($query, $direction)
    {
        return $query->select('business_users.*')
            ->leftJoin('v_business_user_custom_values', 'business_users.id', '=', 'v_business_user_custom_values.business_user_id')
            // codeがnullも条件に加えないと当該カスタム項目で保存していないユーザーが検索から漏れてしまう
            ->whereNull('v_business_user_custom_values.code')
            ->orWhere(function ($q) {
                $q->where('v_business_user_custom_values.code', config('consts.user_custom_items.CODE_BUSINESS_CUSTOMER_KBN'))->where('v_business_user_custom_values.flg', true);
            })
            ->distinct()
            ->orderBy('v_business_user_custom_values.val', $direction);
    }
}
