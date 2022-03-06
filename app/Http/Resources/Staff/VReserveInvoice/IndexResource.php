<?php

namespace App\Http\Resources\Staff\VReserveInvoice;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Staff\AgencyDeposit\IndexResource as AgencyDepositIndexResource;
use App\Http\Resources\Staff\AgencyBundleDeposit\IndexResource as AgencyBundleDepositIndexResource;

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
        /**
         * 入金リレーション
         *
         * 一括請求の場合は agency_bundle_deposits
         * 通常請求の場合は agency_deposits のリスト
         */
        $deposits = ($this->is_pay_altogether == 1) ? AgencyBundleDepositIndexResource::collection($this->agency_bundle_deposits) : AgencyDepositIndexResource::collection($this->agency_deposits);

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

            // 請求行タイプ
            'is_pay_altogether' => $this->is_pay_altogether, // 一括請求の場合は1、それ以外は0
            'bundle_id' => $this->reserve_bundle_invoice_hash_id, // reserve_bundle_invoice_idのハッシュ値。一括請求書の内訳ページへの遷移等に使用
            'reserve_bundle_invoice_id' => $this->reserve_bundle_invoice_id,
            'reserve_invoice_id' => $this->reserve_invoice_id,
            'billing_address_name' => $this->billing_address_name,
            'applicant_name' => $this->applicant_name,
            'issue_date' => $this->issue_date,
            'payment_deadline' => $this->payment_deadline,
            'departure_date' => $this->departure_date,
            'amount_total' => $this->amount_total,
            'deposit_amount' => $this->deposit_amount,
            'not_deposit_amount' => $this->not_deposit_amount,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'manager_id' => $this->last_manager_id, // 担当者(最終更新値)
            'note' => $this->last_note, // 備考(最終更新値)
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            // リレーション
            'reserve' => [
                'control_number' => $this->reserve->control_number,
                'updated_at' => $this->reserve->updated_at ? $this->reserve->updated_at->format('Y-m-d H:i:s') : null, // 予約情報の変更を検知するために使用
                'is_deleted' => $this->reserve->trashed(),
                'is_canceled' => $this->reserve->is_canceled,
            ],
            // 入金リスト（通常/一括請求）
            'combination_deposits' => $deposits,
        ];
    }
}
