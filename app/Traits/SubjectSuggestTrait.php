<?php

namespace App\Traits;

/**
 * 科目用のサジェストselectメニューを扱うtrait
 */
trait SubjectSuggestTrait
{
    /**
     * selectメニューで使用するフォーマット配列を取得
     *
     * !!!!! ここを変更する場合は、app/Http/Resources/Staff/Subject ディレクトリ内のファイルも変更する
     */
    public function getSelectRow($row) : array
    {
        $base = ['label' => "{$row->code}{$row->name}", 'value' => $row->id];

        $customValues = collect([]);
        if (get_class($row) === 'App\Models\SubjectHotel') {
            // カスタム項目
            $customValues = $row->v_subject_hotel_custom_values->mapWithKeys(function ($item) {
                return [$item['key'] => $item['val']];
            });

            $data = [
                // "id" => $row->id,
                "name" => $row->name,
                "hotel_name" => $row->hotel_name,
                "address" => $row->address,
                "tel" => $row->tel,
                "fax" => $row->fax,
                "url" => $row->url,
                "code" => $row->code,
                "ad_gross_ex" => $row->ad_gross_ex,
                "ad_gross" => $row->ad_gross,
                "ad_cost" => $row->ad_cost,
                "ad_commission_rate" => $row->ad_commission_rate,
                "ad_net" => $row->ad_net,
                "ad_zei_kbn" => $row->ad_zei_kbn,
                "ad_gross_profit" => $row->ad_gross_profit,
                "ch_gross_ex" => $row->ch_gross_ex,
                "ch_gross" => $row->ch_gross,
                "ch_cost" => $row->ch_cost,
                "ch_commission_rate" => $row->ch_commission_rate,
                "ch_net" => $row->ch_net,
                "ch_zei_kbn" => $row->ch_zei_kbn,
                "ch_gross_profit" => $row->ch_gross_profit,
                "inf_gross_ex" => $row->inf_gross_ex,
                "inf_gross" => $row->inf_gross,
                "inf_cost" => $row->inf_cost,
                "inf_commission_rate" => $row->inf_commission_rate,
                "inf_net" => $row->inf_net,
                "inf_zei_kbn" => $row->inf_zei_kbn,
                "inf_gross_profit" => $row->inf_gross_profit,
                "note" => $row->note,
                "supplier_id" => $row->supplier_id,
            ];
        } elseif (get_class($row) === 'App\Models\SubjectOption') {
            // カスタム項目
            $customValues = $row->v_subject_option_custom_values->mapWithKeys(function ($item) {
                return [$item['key'] => $item['val']];
            });

            $data = [
                // "id" => $row->id,
                "name" => $row->name,
                "code" => $row->code,
                "ad_gross_ex" => $row->ad_gross_ex,
                "ad_gross" => $row->ad_gross,
                "ad_cost" => $row->ad_cost,
                "ad_commission_rate" => $row->ad_commission_rate,
                "ad_net" => $row->ad_net,
                "ad_zei_kbn" => $row->ad_zei_kbn,
                "ad_gross_profit" => $row->ad_gross_profit,
                "ch_gross_ex" => $row->ch_gross_ex,
                "ch_gross" => $row->ch_gross,
                "ch_cost" => $row->ch_cost,
                "ch_commission_rate" => $row->ch_commission_rate,
                "ch_net" => $row->ch_net,
                "ch_zei_kbn" => $row->ch_zei_kbn,
                "ch_gross_profit" => $row->ch_gross_profit,
                "inf_gross_ex" => $row->inf_gross_ex,
                "inf_gross" => $row->inf_gross,
                "inf_cost" => $row->inf_cost,
                "inf_commission_rate" => $row->inf_commission_rate,
                "inf_net" => $row->inf_net,
                "inf_zei_kbn" => $row->inf_zei_kbn,
                "inf_gross_profit" => $row->inf_gross_profit,
                "note" => $row->note,
                "supplier_id" => $row->supplier_id,
            ];
        } elseif (get_class($row) === 'App\Models\SubjectAirplane') {
            // カスタム項目
            $customValues = $row->v_subject_airplane_custom_values->mapWithKeys(function ($item) {
                return [$item['key'] => $item['val']];
            });

            $data = [
                // "id" => $row->id,
                "name" => $row->name,
                "booking_class" => $row->booking_class,
                "code" => $row->code,
                "ad_gross_ex" => $row->ad_gross_ex,
                "ad_gross" => $row->ad_gross,
                "ad_cost" => $row->ad_cost,
                "ad_commission_rate" => $row->ad_commission_rate,
                "ad_net" => $row->ad_net,
                "ad_zei_kbn" => $row->ad_zei_kbn,
                "ad_gross_profit" => $row->ad_gross_profit,
                "ch_gross_ex" => $row->ch_gross_ex,
                "ch_gross" => $row->ch_gross,
                "ch_cost" => $row->ch_cost,
                "ch_commission_rate" => $row->ch_commission_rate,
                "ch_net" => $row->ch_net,
                "ch_zei_kbn" => $row->ch_zei_kbn,
                "ch_gross_profit" => $row->ch_gross_profit,
                "inf_gross_ex" => $row->inf_gross_ex,
                "inf_gross" => $row->inf_gross,
                "inf_cost" => $row->inf_cost,
                "inf_commission_rate" => $row->inf_commission_rate,
                "inf_net" => $row->inf_net,
                "inf_zei_kbn" => $row->inf_zei_kbn,
                "inf_gross_profit" => $row->inf_gross_profit,
                "note" => $row->note,
                "supplier_id" => $row->supplier_id,
                "departure_id" => $row->departure_id,
                "destination_id" => $row->destination_id,
            ];
        }

        return array_merge($base, $data, $customValues->toArray());
    }
}
