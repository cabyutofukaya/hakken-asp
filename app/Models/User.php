<?php

namespace App\Models;

use App\Models\AppUser;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Lang;

class User extends Model implements AppUser, ApplicantInterface
{
    use SoftDeletes,Notifiable,Sortable,ModelLogTrait;

    // protected $softCascade = [
    //     'user_visas',
    //     'user_mileages',
    //     'user_member_cards',
    //     'agency_consultations', // 当該ユーザーを消したら相談履歴も削除する
    //     // TODO ↓参加者を消した場合と区別がつかなくなるので消さない方が良いかも。一旦検討
    //     // 'participants', ユーザーを消した場合は紐づいた参加者も削除。逆は削除不要
    // ];

    public $sortable = [
        'id',
        'user_number',
        'name',
        'name_kana',
        'name_roman',
        'mobile_phone',
        'email',
        'status',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'user_number',
        'userable_type',
        'userable_id',
        'status', // web_users(userable)でもstatusが設定されている(ログインの有効・無効に使用)が、ASPの管理画面で参照する値は本テーブルの値
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


        // userableがモデルによって削除処理が異なるのでsoftCascadeは使わずに手動削除
        static::deleting(function ($user) {
            $user->userable()->each(function ($r) {
                if (get_class($r) === 'App\Models\AspUser') { //WebUserは削除不可
                    $r->delete();
                }
            });
            $user->user_visas()->each(function ($r) {
                $r->delete();
            });
            $user->user_mileages()->each(function ($r) {
                $r->delete();
            });
            $user->user_member_cards()->each(function ($r) {
                $r->delete();
            });
            $user->agency_consultations()->each(function ($r) {
                $r->delete();
            });
        });
    }

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    /**
     * ポリモーフィックリレーション
     */
    public function userable()
    {
        return $this->morphTo();
    }

    // ビザ情報
    public function user_visas()
    {
        return $this->hasMany('App\Models\UserVisa');
    }

    // マイレージ情報
    public function user_mileages()
    {
        return $this->hasMany('App\Models\UserMileage');
    }

    // メンバーカード情報
    public function user_member_cards()
    {
        return $this->hasMany('App\Models\UserMemberCard');
    }

    // 参加者
    public function participants()
    {
        return $this->hasMany('App\Models\Participant');
    }

    // 相談
    public function agency_consultations()
    {
        return $this->hasMany('App\Models\AgencyConsultation');
    }
    
    /**
     * カスタム項目を全取得（有効な項目のみ flg=1）
     */
    public function v_user_custom_values()
    {
        return $this->hasMany('App\Models\VUserCustomValue')->where('flg', true);
    }

    /**
     * 申し込んだ全予約の取得
     */
    public function reserves()
    {
        return $this->morphMany('App\Models\Reserve', 'applicantable');
    }
    
    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    // 申込者種別値を取得
    public function getApplicantTypeAttribute(): string
    {
        return config('consts.reserves.PARTICIPANT_TYPE_PERSON');
    }

    // 申込者種別名を取得
    public function getApplicantTypeLabelAttribute(): ?string
    {
        $values = Lang::get('values.reserves.participant_type');
        foreach (config("consts.reserves.PARTICIPANT_TYPE_LIST") as $key => $val) {
            if ($val == config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.users.status');
        foreach (config("consts.users.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここまで //////////////

    //////////////////// ローカルスコープ ここから /////////////////////////

    /**
     * 個人顧客の条件
     *
     * 顧客番号がセットされていること。顧客番号未セットレコードは、
     * 予約登録時の参加者データ（ひとまず仮データのような扱い）
     *
     * @param $query
     * @return mixed
     */
    public function scopeRegistered($query)
    {
        return $query->whereNotNull('user_number');
    }

    //////////////////// ローカルスコープ ここまで /////////////////////////


    ///////////////////// ソートメソッド。カラム名が異なるだけで内容は同じ

    /**
     * 名前ソート
     */
    public function nameSortable($query, $direction)
    {
        return $query->select(
            \DB::raw('
                users.*, 
                CASE 
                    WHEN users.userable_type like(\'%AspUser\') THEN asp_users.name 
                    WHEN users.userable_type like(\'%WebUser\') THEN web_users.name 
                END
                as name 
            ')
        )->leftJoin(
            "asp_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "asp_users.id")
                    ->where('users.userable_type', 'like', '%AspUser');
            }
        )->leftJoin(
            "web_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "web_users.id")
                    ->where('users.userable_type', 'like', '%WebUser');
            }
        )->orderBy('name', $direction);
    }

    /**
     * カナソート
     */
    public function nameKanaSortable($query, $direction)
    {
        return $query->select(
            \DB::raw('
                users.*, 
                CASE 
                    WHEN users.userable_type like(\'%AspUser\') THEN asp_users.name_kana 
                    WHEN users.userable_type like(\'%WebUser\') THEN web_users.name_kana 
                END
                as name_kana 
            ')
        )->leftJoin(
            "asp_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "asp_users.id")
                    ->where('users.userable_type', 'like', '%AspUser');
            }
        )->leftJoin(
            "web_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "web_users.id")
                    ->where('users.userable_type', 'like', '%WebUser');
            }
        )->orderBy('name_kana', $direction);
    }

    /**
     * ローマ字ソート
     */
    public function nameRomanSortable($query, $direction)
    {
        return $query->select(
            \DB::raw('
                users.*, 
                CASE 
                    WHEN users.userable_type like(\'%AspUser\') THEN asp_users.name_roman 
                    WHEN users.userable_type like(\'%WebUser\') THEN web_users.name_roman 
                END
                as name_roman 
            ')
        )->leftJoin(
            "asp_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "asp_users.id")
                    ->where('users.userable_type', 'like', '%AspUser');
            }
        )->leftJoin(
            "web_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "web_users.id")
                    ->where('users.userable_type', 'like', '%WebUser');
            }
        )->orderBy('name_roman', $direction);
    }

    /**
     * 携帯番号ソート
     */
    public function mobilePhoneSortable($query, $direction)
    {
        return $query->select(
            \DB::raw('
                users.*, 
                CASE 
                    WHEN users.userable_type like(\'%AspUser\') THEN asp_users.mobile_phone 
                    WHEN users.userable_type like(\'%WebUser\') THEN web_users.mobile_phone 
                END
                as mobile_phone 
            ')
        )->leftJoin(
            "asp_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "asp_users.id")
                    ->where('users.userable_type', 'like', '%AspUser');
            }
        )->leftJoin(
            "web_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "web_users.id")
                    ->where('users.userable_type', 'like', '%WebUser');
            }
        )->orderBy('mobile_phone', $direction);
    }

    /**
     * メールアドレスソート
     */
    public function emailSortable($query, $direction)
    {
        return $query->select(
            \DB::raw('
                users.*, 
                CASE 
                    WHEN users.userable_type like(\'%AspUser\') THEN asp_users.email 
                    WHEN users.userable_type like(\'%WebUser\') THEN web_users.email 
                END
                as email 
            ')
        )->leftJoin(
            "asp_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "asp_users.id")
                    ->where('users.userable_type', 'like', '%AspUser');
            }
        )->leftJoin(
            "web_users",
            function ($join) {
                /** @var \Illuminate\Database\Query\JoinClause $join */
                $join->on('users.userable_id', '=', "web_users.id")
                    ->where('users.userable_type', 'like', '%WebUser');
            }
        )->orderBy('email', $direction);
    }
}
