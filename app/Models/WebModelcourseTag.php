<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WebModelcourseTag extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'web_modelcourse_id',
        'tag',
    ];

    // モデルコース
    public function web_modelcourse()
    {
        return $this->belongsTo('App\Models\WebModelcourse')->withDefault();
    }

}



