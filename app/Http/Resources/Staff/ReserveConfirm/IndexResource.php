<?php

namespace App\Http\Resources\Staff\ReserveConfirm;

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
        // 催行済を表すパラメータ
        $departedQuery = $this->reserve->is_departed ? sprintf('?%s=1', config('consts.const.DEPARTED_QUERY')) : '';

        $controlNumber = null; // 予約or見積番号
        if ($this->reserve->application_step == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $controlNumber = $this->reserve->estimate_number;
        } elseif ($this->reserve->application_step == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $controlNumber = $this->reserve->control_number;
        }

        // 行程番号
        $reserveItineraryControlNumber = $this->reserve_itinerary ? $this->reserve_itinerary->control_number : "";
        
        // 編集URL
        $editUrl = null;
        if ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $editUrl = route('staff.asp.estimates.reserve_confirm.edit', [
                $request->agencyAccount,
                $this->reserve->application_step,
                $controlNumber,
                $reserveItineraryControlNumber,
                $this->confirm_number
            ]) . $departedQuery;
        } elseif ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $editUrl = route('staff.web.estimates.reserve_confirm.edit', [
                $request->agencyAccount,
                $this->reserve->application_step,
                $controlNumber,
                $reserveItineraryControlNumber,
                $this->confirm_number
            ]) . $departedQuery;
        }

        return [
            'id' => $this->id,
            'edit_url' => $editUrl,
            'confirm_number' => $this->confirm_number,
            'title' => data_get($this, "document_setting.title"),
            'issue_date' => $this->issue_date,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'document_address' => $this->document_address,
            'amount_total' => $this->amount_total,
            // 削除不可テンプレートの場合はtrue
            'is_nondelete' => in_array($this->document_quote->code, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), true),
            'pdf' => [
                'id' => $this->pdf ? $this->pdf->getRouteKey() : null
            ],
            'reserve_itinerary' => [
                'control_number' => $reserveItineraryControlNumber,
                'total_gross' => $this->reserve->is_canceled ? ($this->reserve_itinerary->total_cancel_charge ?? 0) : ($this->reserve_itinerary->total_gross ?? 0), // 予約がキャンセル状態、かつ有効行程の場合はキャンセルチャージの合計
            ]
        ];
    }
}
