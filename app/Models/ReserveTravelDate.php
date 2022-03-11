<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class ReserveTravelDate extends Model
{
    use ModelLogTrait,SoftDeletes;

    // protected $softCascade = [
    //     'reserve_schedules'
    // ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_id',
        'reserve_itinerary_id',
        'agency_id',
        'travel_date',
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
        'updated_at',
        'created_at',
        'deleted_at',
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
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();

        // なぜかsoftCascadeが動かないのでリレーションレコードは手動で削除
        static::deleting(function ($row) {
            foreach ($row->reserve_schedules as $r) {
                $r->delete();
            }
        });
    }
    
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

    // 旅程
    public function reserve_itinerary()
    {
        return $this->belongsTo('App\Models\ReserveItinerary')->withDefault();
    }

    // 旅行詳細（seq順にソート）
    public function reserve_schedules()
    {
        return $this->hasMany('App\Models\ReserveSchedule')
            ->orderBy('seq', 'asc');
    }

    // 買い掛け金明細
    public function account_payable_details()
    {
        return $this->hasMany('App\Models\AccountPayableDetail');
    }

    // 出金明細
    public function agency_withdrawals()
    {
        return $this->hasMany('App\Models\AgencyWithdrawal');
    }
    
    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////
    
    // 旅行日（日付は「YYYY/MM/DD」形式に変換）
    public function getTravelDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

    // 旅行日（「YYYY年MM月DD日」形式に変換）
    public function getTravelDateJpAttribute(): ?string
    {
        return $this->travel_date ? date('Y年m月d日', strtotime($this->travel_date)) : null;
    }

    // 旅行日の曜日を取得
    public function getTravelDateWeekAttribute(): ?string
    {
        $week = ['日','月','火','水','木','金','土'];
        return $this->travel_date ? $week[date('w', strtotime($this->travel_date))] : null;
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここまで //////////////

}
