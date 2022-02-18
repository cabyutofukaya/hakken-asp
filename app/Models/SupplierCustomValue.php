<?php

namespace App\Models;

use App\Traits\CustomValueTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 仕入先用カスタム項目の値を管理するテーブル
 */
class SupplierCustomValue extends Model
{
    use ModelLogTrait,SoftDeletes,CustomValueTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'supplier_id', 
        'user_custom_item_id', 
        'val',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }
    
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier')->withDefault();
    }

    public function user_custom_item()
    {
        return $this->belongsTo('App\Models\UserCustomItem')->withDefault();
    }
}
