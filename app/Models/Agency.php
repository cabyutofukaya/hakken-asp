<?php

namespace App\Models;

// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
use App\Models\AppUser;
use App\Models\Contract;
use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

class Agency extends Model implements AppUser
{
    use SoftDeletes,Sortable,ModelLogTrait,SoftCascadeTrait;

    // 会社情報を論理削除した際の削除テーブル。
    // 会社情報が論理削除されていればログインできないので他のテーブルの削除はひとまず不要に
    protected $softCascade = [
        // 'contracts',
        // 'agency_roles',
        // 'staffs',
        // 'user_custom_items',
        // 'mail_templates',
        // 'document_commons',
        // 'directions',
        // 'areas',
        // 'cities',
        // 'subject_options',
        // 'subject_airplanes',
        // 'subject_hotels',
        // 'suppliers',
        // 'reserve_confirms',
        // 'account_payables',
    ];

    public $sortable = [
        'id',
        'account',
        'company_name',
        'address',
        'tel',
        'business_scope',
        'registration_type',
        'travel_agency_association',
        'fair_trade_council',
        'iata',
        'etbt',
        'bond_guarantee',
        'status',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'identifier',
        'company_name',
        'company_kana',
        'representative_name',
        'representative_kana',
        'person_in_charge_name',
        'person_in_charge_kana',
        'zip_code',
        'prefecture_code',
        'address1',
        'address2',
        'capital',
        'email',
        'tel',
        'fax',
        'emergency_contact',
        'establishment_at',
        'travel_agency_registration_at',
        'business_scope',
        'employees_number',
        'registered_administrative_agency',
        'registration_type',
        'registration_number',
        'travel_agency_association',
        'fair_trade_council',
        'iata',
        'etbt',
        'bond_guarantee',
        'number_staff_allowed',
        'max_storage_capacity',
        'status',
        'registration_at',
        'manager',
        // 契約関連
        'trial',
        'trial_start_at',
        'trial_end_at',
        'definitive',
        'contract_count',
        'agreement_file',
        'terms_file',
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
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'number_staff_allowed' => 'integer',
        'max_storage_capacity' => 'integer',
        'email_verified_at' => 'datetime',
        'fair_trade_council' => 'boolean',
        'iata' => 'boolean',
        'etbt' => 'boolean',
        'bond_guarantee' => 'boolean',
        // 契約関連
        'trial' => 'boolean',
        'trial_start_at' => 'datetime',
        'trial_end_at' => 'datetime',
        'definitive' => 'boolean',
        'contract_count' => 'integer',
    ];

    protected $dates = [
        'establishment_at',
        'travel_agency_registration_at',
        'registration_at',
        'created_at',
        'updated_at',
        'deleted_at',
        // 契約関連
        'trial_start_at',
        'trial_end_at',
    ];


    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // /**
    //  * 親会社
    //  */
    // public function parent_agency()
    // {
    //     return $this->hasOne(Agency::class, 'id', 'parent_id');
    // }

    public function contracts()
    {
        return $this->hasMany('App\Models\Contract');
    }

    public function agency_roles()
    {
        return $this->hasMany('App\Models\AgencyRole');
    }

    public function prefecture()
    {
        return $this->hasOne('App\Models\Prefecture', 'code', 'prefecture_code')->withDefault();
    }

    public function staffs()
    {
        return $this->hasMany('App\Models\Staff');
    }

    // 管理ユーザー
    public function master_staff()
    {
        return $this->hasOne('App\Models\Staff')->where('master', true)->withDefault();
    }

    public function user_custom_items()
    {
        return $this->hasMany('App\Models\UserCustomItem');
    }

    // メール定型文設定
    public function mail_templates()
    {
        return $this->hasMany('App\Models\MailTemplate');
    }

    // 帳票共通設定
    public function document_commons()
    {
        return $this->hasMany('App\Models\DocumentCommon');
    }

    // 方面
    public function directions()
    {
        return $this->hasMany('App\Models\Direction');
    }

    // 国・地域
    public function areas()
    {
        return $this->hasMany('App\Models\Area');
    }

    // 都市・空港
    public function cities()
    {
        return $this->hasMany('App\Models\City');
    }

    // オプション科目
    public function subject_options()
    {
        return $this->hasMany('App\Models\SubjectOption');
    }

    // 航空券科目
    public function subject_airplanes()
    {
        return $this->hasMany('App\Models\SubjectAirplane');
    }

    // ホテル科目
    public function subject_hotels()
    {
        return $this->hasMany('App\Models\SubjectHotel');
    }

    // 仕入先
    public function suppliers()
    {
        return $this->hasMany('App\Models\Supplier');
    }

    // 予約確認書
    public function reserve_confirms()
    {
        return $this->hasMany('App\Models\ReserveConfirm');
    }

    // 仕入れ先買掛金
    public function account_payables()
    {
        return $this->hasMany('App\Models\AccountPayable');
    }

    // 会社情報(HAKKEN用)
    public function web_company()
    {
        return $this->hasOne('App\Models\WebCompany')->withDefault();
    }
    
    // /**
    //  * 仕入先の振込先
    //  */
    // public function supplier_account_payable()
    // {
    //     return $this->hasMany('App\Models\SupplierAccountPayable');
    // }
    // // 所属
    // public function shozoku_agency()
    // {
    //     return $this->user_custom_items()->where('code', config('consts.user_custom_items.CODE_STAFF_SHOZOKU'));
    // }

    // トライアル有効の場合はtrue
    public function is_trial()
    {
        if ($this->trial) {
            $dt = new Carbon(date('Y-m-d'));
            return $dt->between($this->trial_start_at, $this->trial_end_at);
        }
        return false;
    }

    /**
     * 現時点で主となっている契約情報を取得
     *
     * ・契約情報が複数ある場合はstart_atが古い方を有効とする
     * ・end_atの日時そのままで判定すると契約更新処理を実行している最中は
     * 未契約状態になってしまうので若干時間にマージンを設けて判定（AGENCY_CONTRACT_EFFECTIVE_MARGIN）
     */
    public function current_contract() : ?Contract
    {
        return $this->contracts()->whereBetween(DB::raw('NOW()'), [DB::raw('start_at'), DB::raw('end_at + INTERVAL ' . config('consts.const.AGENCY_CONTRACT_EFFECTIVE_MARGIN') . ' MINUTE')])->orderBy('start_at', 'asc')->first();
    }

    /**
     * 正式版有効の場合はtrue
     * 現在有効な契約があるかどうかで判断
     */
    public function is_definitive() : bool
    {
        if ($this->definitive) {
            return $this->current_contract() ? true : false;
        }
        return false;
    }

    // /**
    //  * 契約解約済の場合はtrue
    //  */
    // public function is_cancellation() : bool
    // {
    // }

    // ログインURLを取得
    public function getLoginUrlAttribute(): string
    {
        return route("staff.login", ['agencyAccount' => $this->account]);
    }
    
    /**
     * 郵便番号をハイフン形式で
     */
    public function getZipCodeHyphenAttribute(): string
    {
        return sprintf("%s-%s", substr($this->zip_code, 0, 3), substr($this->zip_code, 3));
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
        $values = Lang::get('values.agencies.status');
        foreach (config("consts.agencies.STATUS_LIST") as $key => $val) {
            if ($val==$this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 業務範囲値を文字に変換
     */
    public function getBusinessScopeLabelAttribute(): ?string
    {
        $values = Lang::get('values.agencies.business_scope');
        foreach (config("consts.agencies.BUSINESS_SCOPE_LIST") as $key => $val) {
            if ($val==$this->business_scope) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 登録種別値を文字に変換
     */
    public function getRegistrationTypeLabelAttribute(): ?string
    {
        $values = Lang::get('values.agencies.registration_type');
        foreach (config("consts.agencies.REGISTRATION_TYPE_LIST") as $key => $val) {
            if ($val==$this->registration_type) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 旅行業協会値を文字に変換
     */
    public function getTravelAgencyAssociationLabelAttribute(): ?string
    {
        $values = Lang::get('values.agencies.travel_agency_association');
        foreach (config("consts.agencies.TRAVEL_AGENCY_ASSOCIATION_LIST") as $key => $val) {
            if ($val==$this->travel_agency_association) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 旅公取協値を文字に変換
     */
    public function getFairTradeCouncilLabelAttribute(): string
    {
        return $this->fair_trade_council ? 'あり' : 'なし';
    }

    /**
     * IATA加入値を文字に変換
     */
    public function getIataLabelAttribute(): string
    {
        return $this->iata ? 'あり' : 'なし';
    }

    /**
     * e-TBT加入値を文字に変換
     */
    public function getEtbtLabelAttribute(): string
    {
        return $this->etbt ? 'あり' : 'なし';
    }

    /**
     * ボンド保証制度値を文字に変換
     */
    public function getBondGuaranteeLabelAttribute(): string
    {
        return $this->bond_guarantee ? 'あり' : 'なし';
    }
    
    // 以下日付カラムの形式変更

    // 登録年月日
    public function getRegistrationAtAttribute($value) : string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }
    public function setRegistrationAtAttribute($value)
    {
        $this->attributes['registration_at'] = $value ? date('Y-m-d', strtotime($value)) : null;
    }

    // 設立年月日（日付は「YYYY/MM/DD」形式に変換）
    public function getEstablishmentAtAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }
    public function setEstablishmentAtAttribute($value)
    {
        $this->attributes['establishment_at'] = $value ? date('Y-m-d', strtotime($value)) : null;
    }

    // 旅行業登録年月日（日付は「YYYY/MM/DD」形式に変換）
    public function getTravelAgencyRegistrationAtAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }
    public function setTravelAgencyRegistrationAtAttribute($value)
    {
        $this->attributes['travel_agency_registration_at'] = $value ? date('Y-m-d', strtotime($value)) : null;
    }

    // 開始日
    public function getTrialStartAtAttribute($value) : ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }
    public function setTrialStartAtAttribute($value)
    {
        $this->attributes['trial_start_at'] = $value ? date('Y-m-d', strtotime($value)) : null;
    }

    // 終了日
    public function getTrialEndAtAttribute($value) : ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }
    public function setTrialEndAtAttribute($value)
    {
        $this->attributes['trial_end_at'] = $value ? date('Y-m-d', strtotime($value)) : null;
    }
    
    //// ソート

    /**
     * 住所によるソートメソッド
     */
    public function addressSortable($query, $direction)
    {
        return $query->orderBy('prefecture_code', $direction)->orderBy('address1', $direction)->orderBy('address2', $direction);
    }
}
