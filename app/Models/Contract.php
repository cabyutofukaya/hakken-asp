<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 対旅行会社との契約管理テーブル
 */
class Contract extends Model
{
    use SoftDeletes, ModelLogTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id', 'start_at', 'end_at', 'contract_plan_id', 'cancellation_at'
    ];

    protected $dates = [
        'start_at',
        'end_at',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
    
    public function contract_plan()
    {
        return $this->belongsTo("App\Models\ContractPlan")->withDefault();
    }

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency');
    }

    /**
     * 親契約ID
     * 
     * テーブル内リレーション
     * 契約更新対象となる前回の契約ID
     * 
     */
    public function parent_contract()
    {
        return $this->hasOne(Contract::class, 'id', 'parent_id');
    }

}
