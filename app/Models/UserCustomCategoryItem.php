<?php

namespace App\Models;

use Lang;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class UserCustomCategoryItem extends Model
{
    public $timestamps = false;

    public function user_custom_category()
    {
        return $this->belongsTo('App\Models\UserCustomCategory')->withDefault();
    }

    /**
     * カスタム項目
     */
    public function user_custom_items()
    {
        return $this->hasMany('App\Models\UserCustomItem')->orderBy('seq', 'asc');
    }

    /**
     * カスタム項目。会社毎に取得
     */
    public function user_custom_items_for_agency()
    {
        return $this->hasMany('App\Models\UserCustomItem')->where('agency_id', auth('staff')->user()->agency->id)->orderBy('seq', 'asc');
    }

    /**
     * 設置場所
     * 
     * 値がカンマ区切りで保存されているので、
     * 「値 => ラベル文字列」形式の配列に変換する
     */
    public function getDisplayPositionsAttribute($value): ?array
    {
        if (!$value) {
            return null;
        }

        // 値 => ラベル 形式の配列を作成
        $positionValues = Lang::get('values.user_custom_items.position');
        foreach (config("consts.user_custom_items.POSITION_LIST") as $key => $val) {
            $positions[$val] = Arr::get($positionValues, $key);
        }

        $res = [];

        // display_positionsカラムの値とマッピング
        foreach (explode(",", $value) as $v) {
            $res[$v] = Arr::get($positions, $v);
        }

        return $res;
    }
}
