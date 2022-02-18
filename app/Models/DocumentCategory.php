<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

/**
 * 帳票設定のカテゴリ名管理テーブル
 */
class DocumentCategory extends Model
{
    use SoftCascadeTrait;

    public $timestamps = false;

    protected $softCascade = ['document_commons', 'document_quotes', 'document_requests'];

    // 共通設定
    public function document_commons()
    {
        return $this->hasMany('App\Models\DocumentCommon');
    }

    // 見積/予約確認書設定
    public function document_quotes()
    {
        return $this->hasMany('App\Models\DocumentQuote');
    }

    // 請求書設定
    public function document_requests()
    {
        return $this->hasMany('App\Models\DocumentRequest');
    }

    // 請求書一括設定
    public function document_request_alls()
    {
        return $this->hasMany('App\Models\DocumentRequestAll');
    }

    // 領収書設定
    public function document_receipts()
    {
        return $this->hasMany('App\Models\DocumentReceipt');
    }
}
