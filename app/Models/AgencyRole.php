<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

/**
 * 役割管理
 *
 * スタッフに紐づける役割情報
 */
class AgencyRole extends Model
{
    use ModelLogTrait, Sortable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'name', 
        'description', 
        'authority',
        'master',
    ];

    public $sortable = ['name', 'description', 'staffs_count'];

    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    public function staffs()
    {
        return $this->hasMany("App\Models\Staff");
    }

    public function staffsCountSortable($query, $direction)
    {
        return $query->orderBy('staffs_count', $direction);
    }

    /**
     * 権限詳細
     */
    public function getAuthorityAttribute($value): ?object
    {
        return $value ? json_decode($value) : null;
    }

    public function setAuthorityAttribute($value)
    {
        $this->attributes['authority'] = $value ? json_encode($value) : null;
    }
}
