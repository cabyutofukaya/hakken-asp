<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class WebMessageHistory extends Model
{
    use SoftDeletes, Sortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'message_log',
        'last_received_at',
        'reserve_status',
    ];

    public $sortable = [
        'id',
        'application_date',
        'manager',
        'last_received_at',
        'reserve_status',
    ];

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 予約
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
    }

    // 最新受信日（日付は「YYYY/MM/DD HH:II」形式に変換）
    public function getLastReceivedAtAttribute($value): ?string
    {
        return $value ? date('Y/m/d H:i', strtotime($value)) : null;
    }

    /**
     * 申込日ソートメソッド
     */
    public function applicationDateSortable($query, $direction)
    {
        return $query->select('web_message_histories.*')
            ->leftJoin('v_reserve_custom_values', 'web_message_histories.reserve_id', '=', 'v_reserve_custom_values.reserve_id')
            ->where('v_reserve_custom_values.code', config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE'))
            ->where('v_reserve_custom_values.flg', true)
            ->orderBy('v_reserve_custom_values.val', $direction);
    }

    /**
     * 自社担当ソートメソッド
     */
    public function managerSortable($query, $direction)
    {
        return $query->select('web_message_histories.*')
            ->leftJoin('reserves', 'web_message_histories.reserve_id', '=', 'reserves.id')
            ->leftJoin('staffs', 'reserves.manager_id', '=', 'staffs.id')
            ->orderBy('staffs.name', $direction);
    }

}

