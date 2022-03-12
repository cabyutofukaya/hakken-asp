<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Traits\HashidsTrait;
use App\Traits\ModelLogTrait;
use App\Traits\DocumentModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 帳票共通設定モデル
 */
class DocumentCommon extends Model
{
    use HashidsTrait, Sortable, SoftDeletes, ModelLogTrait, DocumentModelTrait;
    
    public $sortable = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'document_category_id',
        'name',
        'description',
        'setting',
        'company_name',
        'supplement1',
        'supplement2',
        'zip_code',
        'address1',
        'address2',
        'tel',
        'fax',
        'code',
        'undelete_item',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
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
