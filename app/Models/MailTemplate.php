<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use App\Traits\HashidsTrait;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailTemplate extends Model
{
    use Sortable, ModelLogTrait, HashidsTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'name',
        'description',
        'subject',
        'body',
        'setting',
    ];

    public $sortable = ['id'];
    
    public static function boot()
    {
        parent::boot();
        self::saveModelLog();
    }

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency');
    }

    /**
     * 独自タグのオプション設定
     */
    public function getSettingAttribute($value): ?object
    {
        return $value ? json_decode($value) : null;
    }

    public function setSettingAttribute($value)
    {
        $this->attributes['setting'] = $value ? json_encode($value) : null;
    }
}
