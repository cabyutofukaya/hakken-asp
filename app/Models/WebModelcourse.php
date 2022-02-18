<?php

namespace App\Models;

use App\Traits\ModelLogTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class WebModelcourse extends Model
{
    use SoftDeletes,ModelLogTrait,Sortable,SoftCascadeTrait;

    protected $softCascade = [
        'web_modelcourse_photo' // 写真を論理削除。web_modelcourse_tagsは論理削除非対応なのでそのまま残しておいて良いと思われる
    ];

    public $sortable = [
        'course_no',
        'departure.name',
        'destination.name',
        'name',
        'author.name',
        'price_per_ad',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'author_id',
        'course_no',
        'name',
        'description',
        'stays',
        'price_per_ad',
        'price_per_ch',
        'price_per_inf',
        'departure_id',
        'departure_place',
        'destination_id',
        'destination_place',
        'show',
    ];

    // 旅行会社
    public function agency()
    {
        return $this->belongsTo('App\Models\Agency')->withDefault();
    }

    // 作成者
    public function author()
    {
        return $this->belongsTo('App\Models\Staff', 'author_id')
            ->withTrashed() // 論理削除も取得
            ->withDefault();
    }

    // 出発地
    public function departure()
    {
        return $this->belongsTo('App\Models\VArea', 'departure_id', 'uuid')->withDefault();
    }
    
    // 目的地
    public function destination()
    {
        return $this->belongsTo('App\Models\VArea', 'destination_id', 'uuid')->withDefault();
    }

    // タグ
    public function web_modelcourse_tags()
    {
        return $this->hasMany('App\Models\WebModelcourseTag');
    }

    // メイン写真
    public function web_modelcourse_photo()
    {
        return $this->hasOne('App\Models\WebModelcoursePhoto')->withDefault();
    }
}
