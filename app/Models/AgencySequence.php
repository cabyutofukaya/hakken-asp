<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencySequence extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'current_number', 'updated_at',
    ];

}
