<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Lang;

/**
 * 仕入科目の参加者料金計算に使用するトレイト
 */
trait ParticipantPriceTrait
{
    /**
     * 初期化データ。料金情報は年齢区分に応じたものをセット
     * 
     * @param string $participantId 参加者ID（ハッシュ）
     * @param string $ageKbn 年齢区分
     * @param array $subjectable 料金設定データ
     */
    public function getInitialData(string $participantId, ?string $ageKbn, array $subject) : array
    {
        $priceData = ['zei_kbn' => config('consts.subject_categories.ZEI_KBN_DEFAULT')]; // 税区分
        if ($ageKbn === config('consts.users.AGE_KBN_AD')) {
            foreach ([
                'ad_gross_ex' => 'gross_ex',
                'ad_zei_kbn' => 'zei_kbn',
                'ad_gross' => 'gross',
                'ad_cost' => 'cost',
                'ad_commission_rate' => 'commission_rate',
                'ad_net' => 'net',
                'ad_gross_profit' => 'gross_profit',
                ] as $from => $to) {
                $priceData[$to] = Arr::get($subject, $from);
            }
        } elseif ($ageKbn === config('consts.users.AGE_KBN_CH')) {
            foreach ([
                'ch_gross_ex' => 'gross_ex',
                'ch_zei_kbn' => 'zei_kbn',
                'ch_gross' => 'gross',
                'ch_cost' => 'cost',
                'ch_commission_rate' => 'commission_rate',
                'ch_net' => 'net',
                'ch_gross_profit' => 'gross_profit',
                ] as $from => $to) {
                $priceData[$to] = Arr::get($subject, $from);
            }
        } elseif ($ageKbn === config('consts.users.AGE_KBN_INF')) {
            foreach ([
                'inf_gross_ex' => 'gross_ex',
                'inf_zei_kbn' => 'zei_kbn',
                'inf_gross' => 'gross',
                'inf_cost' => 'cost',
                'inf_commission_rate' => 'commission_rate',
                'inf_net' => 'net',
                'inf_gross_profit' => 'gross_profit',
                ] as $from => $to) {
                $priceData[$to] = Arr::get($subject, $from);
            }
        }
        return array_merge(
            ['participant_id' => $participantId, 'valid' => 1, 'age_kbn' => $ageKbn],
            $priceData,
        );// 有効フラグはoffで初期化
    }

}
