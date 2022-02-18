<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReserveConfirmBusinessUserManager extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_name',
        'department_name',
        'name',
        'honorific',
        'zip_code',
        'prefecture',
        'address1',
        'address2',
    ];

    protected $guarded = [
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // 宛名種別名を取得
    public function getAddressTypeAttribute(): string
    {
        return config('consts.reserves.PARTICIPANT_TYPE_BUSINESS');
    }

}
