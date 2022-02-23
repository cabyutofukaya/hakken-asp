<?php

namespace App\Models;

use App\Models\WebReserveExt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

/**
 * Web相談データ
 *
 */
class WebConsult extends Model
{
    use SoftDeletes;
    
    // Web顧客
    public function web_user()
    {
        return $this->belongsTo('App\Models\WebUser')->withDefault();
    }

    // Web予約情報
    public function web_reserve_exts()
    {
        return $this->hasMany('App\Models\WebReserveExt');
    }

    // 受付可能数に達しているか
    public function is_reach_consult_max() : bool
    {
        return WebReserveExt::where('web_consult_id', $this->id)->whereNotNull('consent_at')->count() >= config('consts.const.WEB_CONSULT_MAX_UNDERTAKE');
    }
    
    // ミューテタ

    /**
     * 予算表示（総額/一人当たり¥00,000）
     */
    public function getBudgetLabelAttribute($value): string
    {
        $kbns = get_const_item('web_consults', 'budget_kbn');
        return Arr::get($kbns, $this->budget_kbn) . "￥" . number_format($this->amount);
    }

    /**
     * 旅行日表示
     */
    public function getDepartureLabelAttribute($value): string
    {
        if ($this->departure_kbn == config('consts.web_consults.DEPARTURE_KBN_DATE')) { // 日付
            $weekday = ['日', '月', '火', '水', '木', '金', '土'];

            $date = new Carbon($this->departure_date);
            return $date->format('Y年n月j日') . "(". $weekday[$date->dayOfWeek] .")";
        } elseif ($this->departure_kbn == config('consts.web_consults.DEPARTURE_KBN_SEASON')) { // 時期
            return $this->departure_season;
        } elseif ($this->departure_kbn == config('consts.web_consults.DEPARTURE_KBN_UNDECIDED')) { //　未定
            return '時期未定';
        } else { // 該当なし
            return '';
        }
    }

    /**
     * 宿泊表示
     */
    public function getStaysLabelAttribute($value): string
    {
        $stays = get_const_item('web_modelcourses', 'stay');
        return Arr::get($stays, $this->stays);
    }

    /**
     * 見積ステータス値を文字に変換
     */
    public function getEstimateStatusLabelAttribute(): ?string
    {
        $values = \Lang::get('values.web_reserve_exts.estimate_status');
        foreach (config("consts.web_reserve_exts.ESTIMATE_STATUS_LIST") as $key => $val) {
            if ($val==$this->estimate_status) {
                return Arr::get($values, $key);
            }
        }
        return null;
    }

    // 取消日時（日付は「YYYY/MM/DD」形式に変換）
    public function getCancelAtAttribute($value): ?string
    {
        return $value ? date('Y/m/d H:i', strtotime($value)) : null;
    }

    /**
     * 興味があること
     */
    public function getInterestAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }
}
