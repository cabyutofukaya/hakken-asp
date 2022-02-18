<?php

namespace App\Repositories\Common;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

// 主に子要素に共通するメソッド
interface ChildElementRepositoryInterface
{
  public function updateOrCreate(array $attributes, array $values = []) : Model;
  
  public function deleteExceptionGenKey(string $genKey, int $id, bool $isSoftDelete): bool;
}

