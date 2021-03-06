<?php

namespace App\Http\Resources\Staff\AccountPayable;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'url' => $this->reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE') ? route('staff.management.payment.item', [$request->agencyAccount, $this->reserve->hash_id, $this->supplier->hash_id]) : null,
            'payment_deadline' => $this->enabled_account_payable_details->isNotEmpty() ? $this->enabled_account_payable_details()->latest('payment_date')->first()->payment_date : null, // enabled_account_payable_detailsリレーションの中で最も新しい日付を取得（支払日が一番遠い日）
            'latest_withdrawal_date' => $this->agency_withdrawals->isNotEmpty() ? $this->agency_withdrawals()->latest("created_at")->first()->withdrawal_date : null,// 最後に出金した日付を取得
            'sum_net' => $this->sum_net, // 無効仕入を含んだ合計
            // 'sum_enabled_net' => $this->sum_enabled_net, // 無効仕入を除いた合計
            'sum_withdrawal' => $this->sum_withdrawal,
            'supplier_name' => $this->supplier_name,
        ];
    }
}
