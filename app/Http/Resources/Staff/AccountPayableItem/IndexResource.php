<?php

namespace App\Http\Resources\Staff\AccountPayableItem;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Staff\AgencyWithdrawalItemHistory\IndexResource as AgencyWithdrawalItemHistoryIndexResource;

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
        // 各種URL。ASP申し込みとWEB申し込み出し分け
        $reserveUrl = ''; // 詳細ページURL

        if (optional($this->reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $reserveUrl = route('staff.asp.estimates.reserve.show', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);

        } elseif (optional($this->reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $reserveUrl = route('staff.web.estimates.reserve.show', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);

        }

        return [
            'id' => $this->id,
            'url' => route('staff.management.payment.detail', ['agencyAccount' => $request->agencyAccount, 'reserveHashId' => optional($this->reserve)->hash_id, optional($this->supplier)->hash_id, $this->subject, $this->item_hash_id]),
            'reserve_url' => $reserveUrl,
            'total_purchase_amount' => $this->total_purchase_amount,
            'total_amount_accrued' => $this->total_amount_accrued,
            'payment_date' => $this->payment_date,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name,
            'item_code' => $this->item_code,
            'item_name' => $this->item_name,
            'manager_id' => $this->last_manager_id, // 担当者(最終更新値)
            'note' => $this->last_note, // 備考(最終更新値)
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
            // リレーション
            'reserve' => [
                'control_number' => $this->reserve ? $this->reserve->control_number : null,
                'is_deleted' => $this->reserve ? $this->reserve->trashed() : null,
                'is_canceled' => $this->reserve ? $this->reserve->is_canceled : null,
            ],
            'agency_withdrawal_item_histories' => AgencyWithdrawalItemHistoryIndexResource::collection($this->agency_withdrawal_item_histories)
        ];
    }

}
