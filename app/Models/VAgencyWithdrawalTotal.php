<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VAgencyWithdrawalTotal extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
      'total_amount' => 'integer',
    ];
}
