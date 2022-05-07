<?php

namespace App\Models;

use App\Traits\HashidsTrait; // selectメニューなどでスタッフを選ぶ際はハッシュ化されたidを値として使用
use App\Models\AppUser;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Lang;

class Staff extends Authenticatable implements AppUser
{
    use SoftDeletes,Notifiable,Sortable,ModelLogTrait,SoftCascadeTrait;

    protected $table = 'staffs';

    protected $softCascade = [
        // 'user_consultations',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'name',
        'account',
        'password',
        'email',
        'master',
        'agency_role_id',
        'status',
        'number_of_plan',
    ];

    protected $guarded = [
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'agency_role' => 'integer',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','remember_token', 'deleted_at'
    ];
    
    public $sortable = [
        'id',
        'account',
        'name',
        'email',
        'status',
        'agency_role.name',
        'shozoku',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency');
    }

    public function agency_role()
    {
        return $this->belongsTo('App\Models\AgencyRole')->withDefault();
    }

    /**
     * 送信メッセージ
     */
    public function web_messages()
    {
        return $this->morphMany('App\Models\WebMessage', 'senderable');
    }

    // モデルコース(表示のみ)
    public function enabled_web_modelcourses()
    {
        return $this->hasMany('App\Models\WebModelcourse', 'author_id')
            ->where('show', true) // 表示フラグOn
            ->orderBy('created_at', 'DESC'); // 最新順で表示
    }

    /**
     * プロフィール
     */
    public function web_profile()
    {
        return $this->hasOne('App\Models\WebProfile', 'staff_id')->withDefault();
    }

    ///////////////// カスタム項目ここから ///////////////////

    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_staff_custom_values()
    {
        return $this->hasMany('App\Models\VStaffCustomValue')->where('flg', true);
    }

    /**
     * 所属項目を取得（有効な項目のみ対象 flg=1）
     */
    public function shozokus()
    {
        return $this->hasMany('App\Models\VStaffCustomValue')
            ->where('code', config('consts.user_custom_items.CODE_STAFF_SHOZOKU'))
            ->where('flg', true);
    }

    ///////////////// カスタム項目ここまで ///////////////////


    /**
     * 当該対象における操作認可があるか
     *
     * @param string $target 操作対象。基本的にテーブル名
     * @param string $action アクション
     */
    public function isApproval($target, $action)
    {
        if (!$this->agency_role) {
            return false;
        }
        if ($this->agency_role && $this->agency_role->authority) {
            foreach ($this->agency_role->authority as $tbls => $acts) {
                if (in_array($target, explode("|", $tbls), true)) { // 許可対象のテーブル名リストは「|」で区切られている
                    if (in_array($action, $acts, true)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.staffs.status');
        foreach (config("consts.staffs.STATUS_LIST") as $key => $val) {
            if ($val==$this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * スタッフ名
     *
     * ステータスが有効ではないスタッフを表示する際は末尾に (XX) を明記
     * 削除スタッフは（削除）を表記
     */
    public function getNameAttribute($value): ?string
    {
        if ($value) {
            if ($this->trashed()) {
                return sprintf("%s(削除)", $value);
            } else {
                if ($this->status != config('consts.staffs.STATUS_VALID')) {
                    $statuses = get_const_item('staffs', 'status');
                    return sprintf("%s(%s)", $value, Arr::get($statuses, $this->status, ""));
                }
            }
            return $value;
        }
        return null;
    }

    /**
     * スタッフ名
     *
     * 削除済or無効ユーザーでもサフィックスを付けたくないときに使用
     */
    public function getOrgNameAttribute($value): ?string
    {
        if ($this->name && $this->trashed()) {
            return preg_replace('/\(削除\)$/', '', $this->name);
        }
        if ($this->name && $this->status != config('consts.staffs.STATUS_VALID')) {
            return preg_replace('/\(無効\)$/', '', $this->name);
        }
        return $this->name;
        // return $this->name ? preg_replace('/(\(削除\)|\(無効\))$/', '', $this->name) : null;
    }

    /**
     * TODO distinctをつけるとソートできなくなるので再考
     *
     * 所属によるソートメソッド
     */
    public function shozokuSortable($query, $direction)
    {
        return $query->select('staffs.*')
            ->leftJoin('v_staff_custom_values', 'staffs.id', '=', 'v_staff_custom_values.staff_id')
            // codeがnullも条件に加えないと当該カスタム項目で保存していないユーザーが検索から漏れてしまう
            ->whereNull('v_staff_custom_values.code')
            ->orWhere(function ($q) {
                $q->where('v_staff_custom_values.code', config('consts.user_custom_items.CODE_STAFF_SHOZOKU'))->where('v_staff_custom_values.flg', true);
            })
            ->distinct()
            ->orderBy('v_staff_custom_values.val', $direction);
    }


    /// 認可情報(主にサイドメニューで使用)

    // 「予約/見積」参照権限
    public function getIsReserveSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.PARTICIPANTS|RESERVE_CONFIRMS|RESERVE_INVOICES|RESERVE_RECEIPTS|RESERVE_ITINERARIES|RESERVES|WEB_ONLINE_SCHEDULES|WEB_RESERVE_EXTS'), [])
        );
    }

    // 「個人顧客」参照権限
    public function getIsUserSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.USER_MEMBER_CARDS|USER_MILEAGES|USER_VISAS|USERS'), [])
        );
    }

    // 「法人顧客」参照権限
    public function getIsBusinessUserSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.BUSINESS_USERS|BUSINESS_USER_MANAGERS'), [])
        );
    }

    // 「経理業務」参照権限
    public function getIsManagementSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.ACCOUNT_PAYABLE_DETAILS|ACCOUNT_PAYABLE_RESERVES|ACCOUNT_PAYABLES|AGENCY_BUNDLE_DEPOSITS|AGENCY_DEPOSITS|AGENCY_WITHDRAWALS|RESERVE_BUNDLE_INVOICES|RESERVE_BUNDLE_RECEIPTS|RESERVE_INVOICES|RESERVE_RECEIPTS|V_RESERVE_INVOICES'), [])
        );
    }

    // 「システム設定」参照権限
    public function getIsSystemSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.AGENCY_ROLES|DOCUMENT_CATEGORIES|DOCUMENT_COMMONS|DOCUMENT_QUOTES|DOCUMENT_RECEIPTS|DOCUMENT_REQUEST_ALLS|DOCUMENT_REQUESTS|MAIL_TEMPLATES|STAFFS|USER_CUSTOM_ITEMS'), [])
        );
    }

    // 「マスタ管理」参照権限
    public function getIsMasterSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.DIRECTIONS|V_DIRECTIONS|AREAS|V_AREAS|CITIES|SUBJECT_OPTIONS|SUBJECT_AIRPLANES|SUBJECT_HOTELS|SUPPLIERS'), [])
        );
    }

    // 「相談履歴」参照権限
    public function getIsConsultationSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.AGENCY_CONSULTATIONS|WEB_MESSAGES|WEB_MESSAGE_HISTORIES'), [])
        );
    }

    // 「WEBページ管理」参照権限
    public function getIsWebSettingReadPermissionAttribute() : bool
    {
        if (!$this->agency_role) {
            return false;
        }
        return in_array(
            config('consts.agency_roles.READ'),
            data_get($this->agency_role->authority, config('consts.agency_roles.WEB_COMPANIES|WEB_PROFILES|WEB_MODELCOURSES'), [])
        );
    }
}
