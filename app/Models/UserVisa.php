<?php

namespace App\Models;

use App\Traits\HashidsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVisa extends Model
{
    use HashidsTrait, SoftDeletes;

    // 親テーブルとtimestamps連携
    protected $touches = ['user'];
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'user_id',
        'number',
        'country_code',
        'kind',
        'issue_place_code',
        'issue_date',
        'expiration_date',
        'note',
        'gen_key',
    ];

    protected $dates = [
        'updated_at',
    ];

    // 個人顧客
    public function user()
    {
        return $this->belongsTo('App\Models\User')->withDefault();
    }
    
    // 国
    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_code', 'code')->withDefault();
    }

    // 旅券発行国
    public function issue_place()
    {
        return $this->belongsTo('App\Models\Country', 'issue_place_code', 'code')->withDefault();
    }

    
    // 保存時、暗号化が必要なカラムのミューテタとアクセサ

    // 旅券番号
    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = empty($value)
            ? null
            : Crypt::encrypt($value);
    }
    public function getNumberAttribute($value)
    {
        return empty($value)
            ? null
            : Crypt::decrypt($value);
    }
}
