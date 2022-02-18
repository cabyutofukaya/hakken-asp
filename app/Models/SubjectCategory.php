<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

/**
 * 科目設定のカテゴリ名管理テーブル
 */
class SubjectCategory extends Model
{
    use SoftCascadeTrait;

    public $timestamps = false;

    protected $softCascade = [
        'subject_options',
        'subject_airplanes',
        'subject_hotels',
    ];

    // オプション科目
    public function subject_options()
    {
        return $this->hasMany('App\Models\SubjectOption');
    }

    // 航空券科目
    public function subject_airplanes()
    {
        return $this->hasMany('App\Models\SubjectAirplane');
    }

    // ホテル科目
    public function subject_hotels()
    {
        return $this->hasMany('App\Models\SubjectHotel');
    }
}
