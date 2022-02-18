<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HashidsTrait;

/**
 * 帳票でPDFを生成するモデルのスーパークラス（見積・予約、請求書）
 */
class DocumentPdf extends Model
{
    use HashidsTrait, SoftDeletes;

    public $fillable = [
        'agency_id',
        'documentable_type',
        'documentable_id',
        'file_name',
        'original_file_name',
        'mime_type',
        'file_size',
        'description',
    ];

    /**
     * ポリモーフィックリレーション
     */
    public function documentable()
    {
        return $this->morphTo();
    }
}
