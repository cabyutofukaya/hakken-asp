<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * business_usersテーブルのIDを生成する際に使用する連番管理テーブル
 */
class BusinessUserSequence extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'current_number', 
        'updated_at',
    ];

    protected $dates = [
        'updated_at',
    ];
}
