<?php
namespace App\Models;

// 予約申込者用モデル共通メソッド
interface ApplicantInterface
{
  public function getApplicantTypeAttribute(): string;
}
