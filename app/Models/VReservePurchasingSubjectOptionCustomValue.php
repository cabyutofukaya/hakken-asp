<?php

namespace App\Models;

use App\Traits\VCustomValueTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 仕入オプション科目値に親テーブルの
 * 有効フラグや管理コード情報などを付与し、
 * データを取得しやすくするためのviewモデル 
 */
class VReservePurchasingSubjectOptionCustomValue extends Model
{
    use VCustomValueTrait;
}
