<?php

namespace App\Models;

use App\Models\AppUser;
use App\Traits\ModelLogTrait;
use App\Traits\IndividualTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lang;

class AspUser extends Model implements AppUser
{
    use SoftDeletes,Notifiable,ModelLogTrait,SoftCascadeTrait,IndividualTrait;

    protected $touches = ['user'];

    protected $softCascade = [
        'user_ext',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'name',
        'name_kana',
        'name_roman',
        'sex',
        'birthday_y',
        'birthday_m',
        'birthday_d',
        'registration_type',
        'mobile_phone',
        'tel',
        'fax',
        'email',
        'zip_code',
        'prefecture_code',
        'address1',
        'address2',
        'passport_number',
        'passport_issue_date',
        'passport_expiration_date',
        'passport_issue_country_code',
        'citizenship_code',
        'workspace_name',
        'workspace_address',
        'workspace_tel',
        'workspace_note',
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
    }

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    /**
     * 拡張データ
     */
    public function user_ext()
    {
        return $this->hasOne('App\Models\AspUserExt');
    }
}
