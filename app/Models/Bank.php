<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kinyu_code',
        'kinyu_kana',
        'kinyu_name',
        'tenpo_code',
        'tenpo_kana',
        'tenpo_name',
        'zip_code',
        'address',
        'tel',
        'tegata_kokanjyo_no',
        'narabi_code',
        'kamei',
    ];
}
