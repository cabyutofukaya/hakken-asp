<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierAccountPayable extends Model
{
    use SoftDeletes, ModelLogTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'supplier_id',
        'kinyu_code',
        'tenpo_code',
        'kinyu_name',
        'tenpo_name',
        'account_type',
        'account_number',
        'account_name',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
}
