<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
// use Ramsey\Uuid\Uuid;

// プライマリーキーがUUIDの基底class
class UuidModel extends Model
{
    // プライマリーキーのカラム名
    protected $primaryKey = 'id';

    // プライマリーキーの型
    protected $keyType = 'string';

    // プライマリーキーは自動連番か？
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        // レコード作成時にprimary keyに自動的にuuidを入れてくれるようにする
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::orderedUuid();
        });
    }
}
