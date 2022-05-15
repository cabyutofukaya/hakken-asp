<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 仕入先支払い日管理
 */
class SupplierPaymentDate extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserve_id',
        'supplier_id',
        'payment_date',
    ];

    // 予約情報
    public function reserve()
    {
        return $this->belongsTo('App\Models\Reserve')->withDefault();
    }

    // 仕入先
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault();
    }
}
