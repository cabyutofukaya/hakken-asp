<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WebProfileTag extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'web_profile_id',
        'tag',
    ];

    // プロフィール
    public function web_profile()
    {
        return $this->belongsTo('App\Models\WebProfile')->withDefault();
    }

}


