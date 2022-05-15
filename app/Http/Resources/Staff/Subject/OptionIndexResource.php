<?php

namespace App\Http\Resources\Staff\Subject;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * !!!!! 本ファイルを変更する場合は、app\Traits\SubjectSuggestTrait.phpも変更する
 */
class OptionIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // カスタム項目
        $customValues = $this->v_subject_option_custom_values->mapWithKeys(function ($item) {
            return [$item['key'] => $item['val']];
        });

        $base = [
            "id" => $this->id, // IDはname_exカラムに保存する値なので必ず設定
            "name" => $this->name,
            "code" => $this->code,
            "ad_gross_ex" => $this->ad_gross_ex,
            "ad_gross" => $this->ad_gross,
            "ad_cost" => $this->ad_cost,
            "ad_commission_rate" => $this->ad_commission_rate,
            "ad_net" => $this->ad_net,
            "ad_zei_kbn" => $this->ad_zei_kbn,
            "ad_gross_profit" => $this->ad_gross_profit,
            "ch_gross_ex" => $this->ch_gross_ex,
            "ch_gross" => $this->ch_gross,
            "ch_cost" => $this->ch_cost,
            "ch_commission_rate" => $this->ch_commission_rate,
            "ch_net" => $this->ch_net,
            "ch_zei_kbn" => $this->ch_zei_kbn,
            "ch_gross_profit" => $this->ch_gross_profit,
            "inf_gross_ex" => $this->inf_gross_ex,
            "inf_gross" => $this->inf_gross,
            "inf_cost" => $this->inf_cost,
            "inf_commission_rate" => $this->inf_commission_rate,
            "inf_net" => $this->inf_net,
            "inf_zei_kbn" => $this->inf_zei_kbn,
            "inf_gross_profit" => $this->inf_gross_profit,
            "note" => $this->note,
            "supplier_id" => $this->supplier_id,
        ];

        // 配列の階層が深くなると呼び出し元のjavascriptで処理しにくくなるため、フラットな配列で返す
        return array_merge($base, $customValues->toArray());
    }
}
