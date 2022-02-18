<?php

namespace App\Traits;


trait SubjectTrait
{
    // 料金関連項目の初期化（POST元が対象科目と異なる場合は、料金関連項目を削除）
    public function initPriceField($defaultValue)
    {
        return collect($defaultValue)->except([
            'ad_gross_ex',
            'ad_zei_kbn',
            'ad_gross',
            'ad_cost',
            'ad_commission_rate',
            'ad_net',
            'ad_gross_profit',
            'ch_gross_ex',
            'ch_zei_kbn',
            'ch_gross',
            'ch_cost',
            'ch_commission_rate',
            'ch_net',
            'ch_gross_profit',
            'inf_gross_ex',
            'inf_zei_kbn',
            'inf_gross',
            'inf_cost',
            'inf_commission_rate',
            'inf_net',
            'inf_gross_profit',
            ])->toArray();
    }
}
