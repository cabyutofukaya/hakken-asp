<?php

namespace App\Http\Resources\Staff\AgencyWithdrawalItemHistory;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Staff\AgencyWithdrawal\IndexResource as AgencyWithdrawalIndexResource;

class IndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'payment_type' => $this->payment_type,
            'amount' => $this->amount,
            'manager_id' => $this->manager_id,
            'note' => $this->note,
            'record_date' => $this->record_date,
            'withdrawal_date' => $this->withdrawal_date,
            'is_bulk_withdrawal' => $this->is_bulk_withdrawal(), // 一括出金レコードか否か
            'agency_withdrawal_item_history_custom_values' => $this->v_agency_withdrawal_item_history_custom_values->mapWithKeys(function ($item) {
                return [$item['key'] => $item['val']];
            }), // カスタム項目(agency_withdrawal_item_history_custom_values)。一括用
            'agency_withdrawal_custom_values' => $this->agency_withdrawal->v_agency_withdrawal_custom_values->mapWithKeys(function ($item) {
                return [$item['key'] => $item['val']];
            }) // カスタム項目(agency_withdrawal_custom_values)。個別用
        ];

    }
}
