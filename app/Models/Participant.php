<?php

namespace App\Models;

use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

class Participant extends Model
{
    use ModelLogTrait,Sortable,HashidsTrait,SoftDeletes;

    public $sortable = [
        'user.name',
        'created_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'user_id',
        'name',
        'name_kana',
        'name_roman',
        'sex',
        'birthday_y',
        'birthday_m',
        'birthday_d',
        'age',
        'age_kbn',
        'mobile_phone',
        'passport_number',
        'passport_issue_date',
        'passport_expiration_date',
        'passport_issue_country_code',
        'citizenship_code',
        'representative',
        'cancel',
        'note',
    ];

    protected $casts = [
        'cancel' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // 予約
    public function reserves()
    {
        return $this->belongsToMany('App\Models\Reserve');
    }

    // 個人顧客
    public function user()
    {
        return $this->belongsTo('App\Models\User')
            ->withTrashed()
            ->withDefault();
    }

    // 仕入料金（オプション科目）
    public function reserve_participant_option_prices()
    {
        return $this->hasMany('App\Models\ReserveParticipantOptionPrice');
    }

    ////////////////// ゲッター、ミューテタここから ////////////////

    /**
     * 性別値を文字に変換
     */
    public function getSexLabelAttribute(): ?string
    {
        $values = Lang::get('values.users.sex');
        foreach (config("consts.users.SEX_LIST") as $key => $val) {
            if ($val == $this->sex) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 生年月日から年齢を取得
     */
    public function getAgeCalcAttribute(): ?int
    {
        if ($this->birthday_y && $this->birthday_m && $this->birthday_d) {
            $birthday = sprintf("%04d%02d%02d", $this->birthday_y, $this->birthday_m, $this->birthday_d);
            $today = date('Ymd');
            return floor(($today - $birthday) / 10000);
        }
        return $this->age ? $this->age : null;
    }

    /**
     * 年齢区分値を文字に変換
     */
    public function getAgeKbnLabelAttribute(): ?string
    {
        $values = Lang::get('values.users.age_kbn');
        foreach (config("consts.users.AGE_KBN_LIST") as $key => $val) {
            if ($val == $this->age_kbn) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    // 旅券発行日（日付は「YYYY/MM/DD」形式に変換）
    public function getPassportIssueDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 旅券有効期限（日付は「YYYY/MM/DD」形式に変換）
    public function getPassportExpirationDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    /**
     * 状態込みの氏名表記
     *
     * ステータスが有効ではないユーザーを表示する際は末尾に (XX) を明記
     * 削除ユーザーは（削除）を表記。
     * いずれの状態もリレーション先のusersレコードのステータスと連動
     */
    public function getStateIncNameAttribute($value): ?string
    {
        if ($this->name) {
            if ($this->user->trashed()) {
                return sprintf("%s(削除)", $this->name);
            } else {
                if ($this->user->status != config('consts.users.STATUS_VALID')) {
                    $statuses = get_const_item('users', 'status');
                    return sprintf("%s(%s)", $value, Arr::get($statuses, $this->user->status, ""));
                }
            }
            return $this->name;
        }
        return null;
    }

    ////////////////// ゲッター、ミューテタここまで ////////////////
}
