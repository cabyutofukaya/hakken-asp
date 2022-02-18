<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\VCustomValueTrait;

/**
 * 予約用のカスタム項目値に親テーブルの
 * 有効フラグや管理コード情報などを付与し、
 * データを取得しやすくするためのviewモデル 
 */
class VReserveCustomValue extends Model
{
    use VCustomValueTrait;
}
