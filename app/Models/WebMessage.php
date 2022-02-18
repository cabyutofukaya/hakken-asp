<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebMessage extends Model
{
    use SoftDeletes;

    protected $appends = ['sender'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'reserve_id',
        'senderable_type',
        'senderable_id',
        'message',
        'send_at',
        'read_at',
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

    /**
     * 送信者。ポリモーフィックリレーション
     */
    public function senderable()
    {
        return $this->morphTo();
    }

    /**
     * 送信者
     */
    public function getSenderAttribute($value): ?string
    {
        if ($this->senderable_type === 'App\Models\WebUser') {
            return config('consts.web_messages.SENDER_TYPE_USER');
        } elseif ($this->senderable_type === 'App\Models\Staff') {
            return config('consts.web_messages.SENDER_TYPE_CLIENT');
        }
        return null;
    }
}
