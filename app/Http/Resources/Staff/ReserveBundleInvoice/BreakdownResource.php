<?php

namespace App\Http\Resources\Staff\ReserveBundleInvoice;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Staff\AgencyDeposit\IndexResource as AgencyDepositIndexResource;

class BreakdownResource extends JsonResource
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
        $invoiceUrl = ''; // 請求書URL
        $receiptUrl = ''; // 領収書URL

        if (optional($this->reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $reserveUrl = route('staff.asp.estimates.reserve.show', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);
            $invoiceUrl = route('staff.asp.estimates.reserve.invoice.edit', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);
            $receiptUrl = route('staff.asp.estimates.reserve.receipt.edit', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);

        } elseif (optional($this->reserve)->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $reserveUrl = route('staff.web.estimates.reserve.show', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);
            $invoiceUrl = route('staff.web.estimates.reserve.invoice.edit', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);
            $receiptUrl = route('staff.web.estimates.reserve.receipt.edit', [$request->agencyAccount, optional($this->reserve)->control_number ?? '']);

        }

        return [
            'id' => $this->id,
            'reserve_url' => $reserveUrl,
            'invoice_url' => $invoiceUrl,
            'receipt_url' => $receiptUrl,
            'user_invoice_number' => $this->user_invoice_number,
            'applicant_name' => $this->applicant_name,
            'billing_address_name' => $this->billing_address_name,
            'issue_date' => $this->issue_date,
            'payment_deadline' => $this->payment_deadline,
            'departure_date' => $this->departure_date,
            'amount_total' => $this->amount_total,
            'sum_deposit' => $this->sum_deposit, // 入金計
            'sum_not_deposit' => $this->sum_not_deposit, // 未入金計
            'manager_id' => $this->last_manager_id, // 担当者(最終更新値)
            'note' => $this->last_note, // 備考(最終更新値)
            'status' => $this->status,
            'status_label' => $this->status_label,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            // 以下リレーション
            'reserve' => [
                'control_number' => optional($this->reserve)->control_number,
                'updated_at' => optional($this->reserve)->updated_at ? $this->reserve->updated_at->format('Y-m-d H:i:s') : null, // 予約情報の変更を検知するために使用
            ],
            // 入金リスト
            'agency_deposits' => AgencyDepositIndexResource::collection($this->agency_deposits)
        ];
    }
}
