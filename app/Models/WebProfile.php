<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebProfile extends Model
{
    use SoftDeletes,ModelLogTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'staff_id',
        'post',
        'name',
        'name_kana',
        'name_roman',
        'email',
        'tel',
        'sex',
        'birthday_y',
        'birthday_m',
        'birthday_d',
        'introduction',
        'business_area',
        'purpose',
        'interest',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'number_of_plan' => 'integer',
    ];

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // スタッフ
    public function staff()
    {
        return $this->belongsTo('App\Models\Staff')->withDefault();
    }

    // タグ
    public function web_profile_tags()
    {
        return $this->hasMany('App\Models\WebProfileTag');
    }

    // プロフィール写真
    public function web_profile_profile_photo()
    {
        return $this->hasOne('App\Models\WebProfileProfilePhoto')->withDefault();
    }

    // カバー写真
    public function web_profile_cover_photo()
    {
        return $this->hasOne('App\Models\WebProfileCoverPhoto')->withDefault();
    }

    ///////

    /**
     * 対応エリア
     */
    public function getBusinessAreaAttribute($value): array
    {
        if ($value) {
            return explode(config('consts.web_profiles.VALUE_DELIMITER'), trim($value, config('consts.web_profiles.VALUE_DELIMITER')));
        } else {
            return [];
        }
    }

    public function setBusinessAreaAttribute($value)
    {
        if ($value) {
            // ,値,値,値,値,値, の形式で保存
            $this->attributes['business_area'] = sprintf("%s%s%s", config('consts.web_profiles.VALUE_DELIMITER'), implode(config('consts.web_profiles.VALUE_DELIMITER'), $value), config('consts.web_profiles.VALUE_DELIMITER'));
        } else {
            $this->attributes['business_area'] = null;
        }
    }

    /**
     * 旅行分野
     */
    public function getPurposeAttribute($value): array
    {
        if ($value) {
            return explode(config('consts.web_profiles.VALUE_DELIMITER'), trim($value, config('consts.web_profiles.VALUE_DELIMITER')));
        } else {
            return [];
        }
    }

    public function setPurposeAttribute($value)
    {
        if ($value) {
            // ,値,値,値,値,値, の形式で保存
            $this->attributes['purpose'] = sprintf("%s%s%s", config('consts.web_profiles.VALUE_DELIMITER'), implode(config('consts.web_profiles.VALUE_DELIMITER'), $value), config('consts.web_profiles.VALUE_DELIMITER'));
        } else {
            $this->attributes['purpose'] = null;
        }
    }

    /**
     * 旅行内容
     */
    public function getInterestAttribute($value): array
    {
        if ($value) {
            return explode(config('consts.web_profiles.VALUE_DELIMITER'), trim($value, config('consts.web_profiles.VALUE_DELIMITER')));
        } else {
            return [];
        }
    }

    public function setInterestAttribute($value)
    {
        if ($value) {
            // ,値,値,値,値,値, の形式で保存
            $this->attributes['interest'] = sprintf("%s%s%s", config('consts.web_profiles.VALUE_DELIMITER'), implode(config('consts.web_profiles.VALUE_DELIMITER'), $value), config('consts.web_profiles.VALUE_DELIMITER'));
        } else {
            $this->attributes['interest'] = null;
        }
    }
}
