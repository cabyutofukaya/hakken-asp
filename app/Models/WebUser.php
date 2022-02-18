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


        static::deleting(function ($webUser) {
            
            // 各会社に紐づく当該webユーザーを全削除
            \App\Models\User::where('userable_type', 'App\Models\WebUser')->where('userable_id', $webUser->id)->delete();

            $webUser->user_ext()->each(function ($r) {
                $r->delete();
            });
        });
    }

    /**
     * 拡張データ
     */
    public function user_ext()
    {
        return $this->hasOne('App\Models\WebUserExt');
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
