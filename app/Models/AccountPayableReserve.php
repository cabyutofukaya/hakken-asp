<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Kyslik\ColumnSortable\Sortable;
use Lang;

/**
 * 仕入れ先買掛金(予約毎)管理
 */
class AccountPayableReserve extends Model
{
    use ModelLogTrait,SoftDeletes,Sortable;

    public $sortable = [
        'id',
        'reserve.control_number',
        'reserve.departure_date',
        'amount_billed',
        'unpaid_balance',
        'reserve_manager',
        'reserve.note',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'amount_billed',
        'unpaid_balance',
        'status',
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
        'amount_billed' => 'integer',
        'unpaid_balance' => 'integer',
    ];

    protected $dates = [
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 予約
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withTrashed()->withDefault();
    }
    
    //////////////////// ローカルスコープ ここから /////////////////////////

    /**
     * 決定項目（予約ステータスの予約レコードと紐づいたレコード）
     *
     * @param $query
     * @return mixed
     */
    public function scopeDecided($query)
    {
        return $query->whereHas('reserve', function ($q) {
            $q->where('application_step', config('consts.reserves.APPLICATION_STEP_RESERVE'));
        });
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    /**
     * ステータス値を文字に変換
     */
    public function getStatusLabelAttribute(): ?string
    {
        $values = Lang::get('values.account_payable_reserves.status');
        foreach (config("consts.account_payable_reserves.STATUS_LIST") as $key => $val) {
            if ($val == $this->status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    ///////////////// カスタムソート ここから /////////////////

    /**
     * 担当者ソート
     */
    public function reserveManagerSortable($query, $direction)
    {
        return $query->select('account_payable_reserves.*')
            ->leftJoin('reserves', 'account_payable_reserves.reserve_id', '=', 'reserves.id')
            ->leftJoin('staffs', 'reserves.manager_id', '=', 'staffs.id')
            ->orderBy('staffs.name', $direction);
    }

}
