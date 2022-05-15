<?php

namespace App\Models;

use App\Traits\CustomValueTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * (商品毎)出金管理カスタム項目の値を管理するテーブル
 */
class AgencyWithdrawalItemHistoryCustomValue extends Model
{
    use ModelLogTrait,SoftDeletes,CustomValueTrait;

    // // timestamps連携 TODOこれ必要か検討
    // protected $touches = ['agency_withdrawal_item_histories'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_withdrawal_item_history_id',
        'user_custom_item_id',
        'val',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
    
    public function agency_withdrawal_item_history()
    {
        return $this->belongsTo('App\Models\AgencyWithdrawalItemHistory')->withDefault();
    }

    public function user_custom_item()
    {
        return $this->belongsTo('App\Models\UserCustomItem')->withDefault();
    }
}
