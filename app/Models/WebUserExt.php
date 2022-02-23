<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lang;

class WebUserExt extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'agency_id',
        'web_user_id',
        'age',
        'age_kbn',
        'emergency_contact',
        'emergency_contact_column',
        'manager_id',
        'dm',
        'note',
    ];

    // Webユーザー
    public function web_user()
    {
        return $this->belongsTo('App\Models\WebUser')->withDefault();
    }

    /**
     * 自社担当
     * 論理削除も取得
     */
    public function manager()
    {
        return $this->belongsTo('App\Models\Staff', 'manager_id')
            ->withTrashed()
            ->withDefault();
    }

    ///////////////// 読みやすい文字列に変換するAttribute ここから //////////////

    /**
     * DM値を文字に変換
     */
    public function getDmLabelAttribute(): ?string
    {
        $values = Lang::get('values.users.dm');
        foreach (config("consts.users.DM_LIST") as $key => $val) {
            if ($val == $this->dm) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    /**
     * 年齢区分値を文字に変換
     */
    public function getAgeKbnLabelAttribute(): ?string
    {
        $values = Lang::get('values.users.age_kbn');
        foreach (config("consts.users.AGE_KBN_LIST") as $key => $val) {
            if ($val == $this->age_kbn) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

}
