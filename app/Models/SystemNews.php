<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class SystemNews extends Model
{
    use SoftDeletes,Sortable;

    protected $table = 'system_news';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'regist_date',
    ];

    public $sortable = [
        'id',
        'regist_date',
    ];

    // 登録日（日付は「YYYY/MM/DD」形式に変換）
    public function getRegistDateAttribute($value): ?string
    {
        return $value ? date('Y/m/d', strtotime($value)) : null;
    }

}
