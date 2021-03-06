<?php

namespace App\Http\Resources\Staff\AccountPayableDetail;

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
        // 各種URL。ASP申し込みとWEB申し込み出し分け
        $reserveUrl = ''; // 詳細ページURL

        if (optional($this->reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $reserveUrl = route('staff.asp.estimates.reserve.show', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);

        } elseif (optional($this->reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $reserveUrl = route('staff.web.estimates.reserve.show', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);

        }

        return [
            'id' => $this->id,
            'reserve_url' => $reserveUrl,
            'amount_billed' => $this->amount_billed,
            'unpaid_balance' => $this->unpaid_balance,
            'payment_date' => $this->payment_date,
            'use_date' => $this->use_date,
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
            'saleable' => [
                'valid' => $this->saleable->valid ?? 0, // 仕入の有効・無効フラグ。リレーションが取得できない場合は無効で初期化
                'participant' => [
                    'id' => optional($this->saleable->participant)->id,
                    'name' => optional($this->saleable->participant)->name,
                ]
            ],
            'agency_withdrawals' => AgencyWithdrawalIndexResource::collection($this->agency_withdrawals)
        ];
    }

}
