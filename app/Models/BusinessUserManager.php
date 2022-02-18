<?php

namespace App\Models;

use App\Traits\HashidsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Arr;
use Lang;

class BusinessUserManager extends Model implements ApplicantInterface
{
    use HashidsTrait, SoftDeletes;
    
    // 親テーブルとtimestamps連携
    protected $touches = ['business_user'];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'agency_id',
        'business_user_id',
        'user_number',
        'name',
        'name_roman',
        'sex',
        'department_name',
        'email',
        'tel',
        'dm',
        'note',
        'gen_key',
    ];

    protected $dates = [
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'gen_key',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    // 法人顧客
    public function business_user()
    {
        return $this->belongsTo('App\Models\BusinessUser')->withTrashed()->withDefault();
    }

    /**
     * DM値を文字に変換
     */
    public function getDmLabelAttribute(): ?string
    {
        $values = Lang::get('values.business_user_managers.dm');
        foreach (config("consts.business_user_managers.DM_LIST") as $key => $val) {
            if ($val == $this->dm) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    // 申込者種別値を取得
    public function getApplicantTypeAttribute(): string
    {
        return config('consts.reserves.PARTICIPANT_TYPE_BUSINESS');
    }

    // 申込者種別名を取得
    public function getApplicantTypeLabelAttribute(): ?string
    {
        $values = Lang::get('values.reserves.participant_type');
        foreach (config("consts.reserves.PARTICIPANT_TYPE_LIST") as $key => $val) {
            if ($val == config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 担当者名
     *
     * 削除ユーザーは（削除）を表記
     */
    public function getNameAttribute($value): ?string
    {
        if ($this->trashed()) {
            return sprintf("%s(削除)", $value);
        }
        return $value;
    }

    /**
     * 担当者名
     *
     * 削除済ユーザーでも「削除」の表記を付けたくないときに使用
     */
    public function getOrgNameAttribute($value): ?string
    {
        if ($this->name && $this->trashed()) {
            return preg_replace('/\(削除\)$/', '', $this->name);
        }
        return $this->name;
        // return $this->name ? preg_replace('/\(削除\)$/', '', $this->name) : null;
    }
}
