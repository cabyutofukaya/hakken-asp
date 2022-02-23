<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Lang;

/**
 * 個人顧客を扱うModelで使用
 */
trait IndividualTrait
{
    /**
     * ユーザー
     */
    public function user()
    {
        return $this->morphOne('App\Models\User', 'userable');
    }

    // 都道府県
    public function prefecture()
    {
        return $this->belongsTo('App\Models\Prefecture', 'prefecture_code', 'code')->withDefault();
    }

    // 旅券発行国
    public function passport_issue_country()
    {
        return $this->belongsTo('App\Models\Country', 'passport_issue_country_code', 'code')->withDefault();
    }

    // 国籍
    public function citizenship()
    {
        return $this->belongsTo('App\Models\Country', 'citizenship_code', 'code')->withDefault();
    }
    
    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////

    /**
     * 個人顧客名
     *
     * ステータスが有効ではないユーザーを表示する際は末尾に (XX) を明記
     * 削除ユーザーは（削除）を表記
     * ※web_usersテーブルにもstatusがあるが同値はログインの有効・無効を制御する値でcabstation管理画面にて管理(ASP画面からは変更不可)
     */
    public function getNameAttribute($value): ?string
    {
        if ($value) {
            if ($this->user && $this->user->trashed()) { // $this->user は親レコードとなるuserオブジェクト
                return sprintf("%s(削除)", $value);
            } else {
                if ($this->user && $this->user->status != config('consts.users.STATUS_VALID')) {
                    $statuses = get_const_item('users', 'status');
                    return sprintf("%s(%s)", $value, Arr::get($statuses, $this->user->status, ""));
                }
            }
            return $value;
        }
        return null;
    }

    /**
     * 名前
     *
     * 削除済、無効ユーザーでも名前の末尾に同表記を付けたくないときに使用
     */
    public function getOrgNameAttribute($value): ?string
    {
        if ($this->name && $this->user && $this->user->trashed()) {
            return preg_replace('/\(削除\)$/', '', $this->name);
        }
        if ($this->name && $this->user && $this->user->status != config('consts.users.STATUS_VALID')) {
            $statuses = get_const_item('users', 'status');
            return preg_replace('/\('. Arr::get($statuses, $this->user->status) .'\)$/', '', $this->name);
        }
        return $this->name;
    }

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
     * 生年月日から年齢を取得
     */
    public function getAgeCalcAttribute(): ?int
    {
        if ($this->birthday_y && $this->birthday_m && $this->birthday_d) {
            $birthday = sprintf("%04d%02d%02d", $this->birthday_y, $this->birthday_m, $this->birthday_d);
            $today = date('Ymd');
            return floor(($today - $birthday) / 10000);
        }
        return ($this->user_ext && $this->user_ext->age) ? $this->user_ext->age : null;
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
    
    ///////////////// 読みやすい文字列に変換するAttribute ここまで //////////////
}
