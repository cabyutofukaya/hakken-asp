<?php

namespace App\Models;

use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class ReserveSchedule extends Model
{
    use ModelLogTrait,HashidsTrait,SoftDeletes;

    // protected $softCascade = [
    //     'reserve_purchasing_subjects', 
    //     // 'reserve_purchasing_subject_options',
    //     // 'reserve_purchasing_subject_hotels',
    //     // 'reserve_purchasing_subject_airplanes',
    //     'reserve_schedule_photos',
    // ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_travel_date_id',
        'agency_id',
        'type',
        'arrival_time',
        'staying_time',
        'departure_time',
        'place',
        'explanation',
        'transportation',
        'transportation_supplement',
        'seq',
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
        'reserve_travel_date_id',
        'agency_id',
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
            foreach ($row->reserve_purchasing_subjects as $r) {
                $r->delete();
            }
            foreach ($row->reserve_schedule_photos as $r) {
                $r->delete();
            }
        });

    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 旅行日
    public function reserve_travel_date()
    {
        return $this->belongsTo('App\Models\ReserveTravelDate')->withDefault();
    }

    // 仕入科目
    public function reserve_purchasing_subjects()
    {
        return $this->hasMany('App\Models\ReservePurchasingSubject');
    }

    // 写真
    public function reserve_schedule_photos()
    {
        return $this->hasMany('App\Models\ReserveSchedulePhoto');
    }

    // 仕入オプション科目（softCascade用に設定）
    public function reserve_purchasing_subject_options()
    {
        return $this->hasMany('App\Models\ReservePurchasingSubjectOption');
    }

    // 仕入ホテル科目（主にsoftCascade用に設定）
    public function reserve_purchasing_subject_hotels()
    {
        return $this->hasMany('App\Models\ReservePurchasingSubjectHotel');
    }

    // 航空券科目（主にsoftCascade用に設定）
    public function reserve_purchasing_subject_airplanes()
    {
        return $this->hasMany('App\Models\ReservePurchasingSubjectAirplane');
    }
}
