<?php

namespace App\Models;

use App\Traits\CustomValueTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 入金管理カスタム項目の値を管理するテーブル
 */
class AgencyDepositCustomValue extends Model
{
    use ModelLogTrait,SoftDeletes,CustomValueTrait;

    // timestamps連携 TODOこれ必要か検討
    protected $touches = ['agency_deposit'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_deposit_id',
        'user_custom_item_id',
        'val',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
    
    public function agency_deposit()
    {
        return $this->belongsTo('App\Models\AgencyDeposit')->withDefault();
    }

    public function user_custom_item()
    {
        return $this->belongsTo('App\Models\UserCustomItem')->withDefault();
    }

    ////////////////// アクセサとミューテタ


}
