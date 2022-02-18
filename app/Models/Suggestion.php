<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suggestion extends UuidModel
{
    use SoftDeletes;

    // // チャット
    // public function chat()
    // {
    //     return $this->hasOne('App\Models\Chat');
    // }

    // スタッフ
    public function staff()
    {
        return $this->belongsTo('App\Models\Staff');
    }
}
