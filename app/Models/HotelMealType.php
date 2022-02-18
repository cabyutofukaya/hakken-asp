<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelMealType extends Model
{
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'seq',
    ];
}
