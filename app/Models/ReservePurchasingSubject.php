<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class ReservePurchasingSubject extends Model
{
    use ModelLogTrait,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_schedule_id',
        'subjectable_type',
        'subjectable_id',
        // 'seq',
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
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();

        // なぜかsoftCascadeが動かないのでリレーションレコードは手動で削除
        static::deleting(function ($row) {
            $row->subjectable->delete();
        });
    }

    // スケジュール
    public function reserve_schedule()
    {
        return $this->belongsTo('App\Models\ReserveSchedule')->withDefault();
    }

    /**
     * 科目詳細のポリモーフィックリレーション
     */
    public function subjectable()
    {
        return $this->morphTo();
    }
}
