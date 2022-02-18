<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebOnlineSchedule extends Model
{
    use SoftDeletes,ModelLogTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'web_reserve_ext_id',
        'consult_date',
        'requesterable_type',
        'requesterable_id',
        'zoom_api_key_id',
        'zoom_start_url',
        'zoom_join_url',
        'zoom_response',
        'request_status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    protected $hidden = [
        'zoom_response',
    ];

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // API Key情報
    public function zoom_api_key()
    {
        return $this->belongsTo('App\Models\ZoomApiKey')->withDefault();
    }

    /**
     * リクエスト者種別(user or client)
     */
    public function getRequesterAttribute($value): ?string
    {
        if ($this->requesterable_type === 'App\Models\WebUser') {
            return config('consts.web_online_schedules.SENDER_TYPE_USER');
        } elseif ($this->requesterable_type === 'App\Models\Staff') {
            return config('consts.web_online_schedules.SENDER_TYPE_CLIENT');
        }
        return null;
    }

    // 日時（時分まで）
    public function getConsultDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d H:i', strtotime($value)) : null;
    }


    /**
     * zoomレスポンス
     */
    public function getZoomResponseAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setZoomResponseAttribute($value)
    {
        $this->attributes['zoom_response'] = $value ? json_encode($value) : null;
    }
}
