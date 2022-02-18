<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 役割マスタ
 *
 * スタッフに紐づける役割情報
 */
class Role extends Model
{
    use ModelLogTrait;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'name_en', 'authority',
    ];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    /**
     * 権限詳細
     */
    public function getAuthorityAttribute($value): ?object
    {
        return $value ? json_decode($value) : null;
    }
}
