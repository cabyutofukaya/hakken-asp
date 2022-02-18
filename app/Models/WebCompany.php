<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class WebCompany extends Model
{
    use SoftDeletes,ModelLogTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'explanation',
        'logo_image',
        'images',
    ];

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    ///////////////// jsonエンコード・デコード ここから /////////////

    /**
     * イメージ画像
     */
    public function getImagesAttribute($value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }

    public function setImagesAttribute($value)
    {
        // キーが連番になっていないとオブジェクトになってしまうので、array_valuesでソートして確実に配列化
        $this->attributes['images'] = $value ? json_encode(array_values($value)) : null;
    }
}
