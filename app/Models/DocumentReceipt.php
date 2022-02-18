<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 領収書
 */
class DocumentReceipt extends Model
{
    use HashidsTrait, Sortable, SoftDeletes, ModelLogTrait;
    
    public $sortable = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'document_category_id',
        'document_common_id',
        'name',
        'description',
        'title',
        'proviso',
        'note',
        'code',
        'undelete_item',
    ];

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

    // 帳票カテゴリ
    public function document_category()
    {
        return $this->belongsTo('App\Models\DocumentCategory');
    }

    // 宛名・自社情報共通設定
    public function document_common()
    {
        return $this->belongsTo('App\Models\DocumentCommon')->withTrashed()->withDefault();
    }

    ////////////// ゲッター
    /**
     * テンプレート名
     *
     * 削除は（削除）を表記
     */
    public function getNameAttribute($value): ?string
    {
        if ($value) {
            return $this->trashed() ? sprintf("%s(削除)", $value) : $value;
        }
        return null;
    }

}
