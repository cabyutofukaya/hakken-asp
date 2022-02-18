<?php

namespace App\Models;

use App\Traits\HashidsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMemberCard extends Model
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
        'card_name',
        'card_number',
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


    // 保存時、暗号化が必要なカラムのミューテタとアクセサ

    // カード番号
    public function setCardNumberAttribute($value)
    {
        $this->attributes['card_number'] = empty($value)
            ? null
            : Crypt::encrypt($value);
    }
    public function getCardNumberAttribute($value)
    {
        return empty($value)
            ? null
            : Crypt::decrypt($value);
    }
}
