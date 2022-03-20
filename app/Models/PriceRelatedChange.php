<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRelatedChange extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_id', 
        'change_at'
    ];

    protected $dates = [
        'change_at',
    ];
}
