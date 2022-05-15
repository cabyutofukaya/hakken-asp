<?php

namespace App\Http\Resources\Staff\AccountPayableReserve;

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
            'link_url' => route('staff.management.payment.item', ['agencyAccount' => $request->agencyAccount, 'reserveHashId' => optional($this->reserve)->hash_id]), // 仕入先＆商品毎詳細ページURL。reserve_idのパラメータで遷移（遷移先の検索formと連動しないパラメータ）
            'reserve_url' => $reserveUrl,
            'amount_billed' => $this->amount_billed,
            'unpaid_balance' => $this->unpaid_balance,
            'status' => $this->status,
            'status_label' => $this->status_label,
            // 'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
            // リレーション
            'reserve' => [
                'control_number' => optional($this->reserve)->control_number,
                'departure_date' => optional($this->reserve)->departure_date,
                'note' => optional($this->reserve)->note ? mb_strimwidth($this->reserve->note, 0, 30, "...") : null,
                'is_deleted' => optional($this->reserve)->trashed(),
                'is_canceled' => optional($this->reserve)->is_canceled,
                "manager" => [ // 自社担当
                    'name' => optional($this->reserve->manager)->name,
                    'is_deleted' => optional($this->reserve->manager)->trashed()
                ],
            ],
        ];
    }
}
