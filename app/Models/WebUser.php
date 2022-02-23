<?php

namespace App\Models;

use App\Models\AppUser;
use App\Traits\ModelLogTrait;
use App\Traits\IndividualTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class WebUser extends Model implements AppUser
{
    protected $touches = ['user'];
    
    protected $appends = ['org_name']; // "削除"などの状態フレーズのないオリジナルの名前

    use SoftDeletes,Sortable,ModelLogTrait,IndividualTrait;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'web_user_number', // cabさん用の顧客番号。ASPごとの顧客番号はusersテーブルで管理
        'name',
        'name_kana',
        'name_roman',
        'sex',
        'birthday_y',
        'birthday_m',
        'birthday_d',
        'age_kbn',
        'mobile_phone',
        'tel',
        'fax',
        'email',
        'password',
        'zip_code',
        'prefecture_code',
        'address1',
        'address2',
        'passport_number',
        'passport_issue_date',
        'passport_expiration_date',
        'passport_issue_country_code',
        'citizenship_code',
        'registration_type',
        'workspace_name',
        'workspace_address',
        'workspace_tel',
        'workspace_note',
        'email_verified_at',
        'email_verify_token',
        'registed_at',
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
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
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
        'web_user_number',
        'name',
        'name_kana',
        'name_roman',
        'mobile_phone',
        'email',
        'status',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // usersレコード(親)
    public function user()
    {
        // agency_idの条件が"重要"。User対AspUserの関係は1対1だが、User対WebUserの関係は1対多になるので会社IDの条件で1対1の関係にする
        return $this->morphOne('App\Models\User', 'userable')->where('agency_id', auth('staff')->user()->agency_id)->withTrashed(); //削除済みも取得
    }

    /**
     * 拡張データ
     */
    public function user_ext()
    {
        // WebUserを親としているので、1対1の関係にするためにagency_idの条件で絞る
        return $this->hasOne('App\Models\WebUserExt')->where('agency_id', auth('staff')->user()->agency_id);
    }

    /**
     * プロフィール入力が完了している場合はtrue
     * (予約申込が可能なプロフィール登録状態であるか否か)
     * 
     * ※条件を変更する場合はHakken側のWebUserモデルも変更すること
     *
     * @return bool
     */
    public function getIsProfileCompleteAttribute($value): bool
    {
        return $this->name && $this->name_kana && $this->mobile_phone && $this->sex && $this->birthday_y && $this->birthday_m && $this->birthday_d;
    }


    //////////////// スコープ

    /**
     * 本登録ユーザー
     *
     * @param $query
     * @return mixed
     */
    public function scopeDefinitive($query)
    {
        return $query->whereNotNull('registed_at');
    }
}
