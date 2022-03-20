<?php

namespace App\Http\Resources\Staff\ReserveItinerary;

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

        // 編集URL
        $editUrl = null;
        if ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $editUrl = route('staff.asp.estimates.itinerary.edit', [
                $request->agencyAccount,
                $this->reserve->application_step,
                $controlNumber,
                $this->control_number
            ]) . $departedQuery;
        } elseif ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $editUrl = route('staff.web.estimates.itinerary.edit', [
                $request->agencyAccount,
                $this->reserve->application_step,
                $controlNumber,
                $this->control_number
            ]) . $departedQuery;
        }

        // 行程PDF
        $pdfUrl = null;
        if ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $pdfUrl = route('staff.asp.estimates.itinerary.pdf', [
                $this->reserve->agency->account,
                $this->reserve->application_step,
                $controlNumber, // 見積or予約番号
                $this->control_number
            ]);
        } elseif ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $pdfUrl = route('staff.web.estimates.itinerary.pdf', [
                $this->reserve->agency->account,
                $this->reserve->application_step,
                $controlNumber, // 見積or予約番号
                $this->control_number
            ]);
        }

        // ルームリストPDF
        $roomListUrl = null;
        if ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $roomListUrl = $this->reserve_participant_hotel_prices->where('valid', true)->count() > 0 ? route('staff.asp.estimates.itinerary_roominglist.pdf', [
                $this->reserve->agency->account,
                $this->reserve->application_step,
                $controlNumber, // 見積or予約番号
                $this->control_number
            ]) : null; // 有効な宿泊科目情報がある場合はURLを返す
        } elseif ($this->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $roomListUrl = $this->reserve_participant_hotel_prices->where('valid', true)->count() > 0 ? route('staff.web.estimates.itinerary_roominglist.pdf', [
                $this->reserve->agency->account,
                $this->reserve->application_step,
                $controlNumber, // 見積or予約番号
                $this->control_number
            ]) : null; // 有効な宿泊科目情報がある場合はURLを返す
        }

        return [
            "control_number" => $this->control_number,
            "enabled" => $this->enabled,
            "note" => mb_strimwidth($this->note, 0, 30, "..."),
            "total_gross" => ($this->reserve->is_canceled && $this->enabled) ? $this->total_cancel_charge : $this->total_gross, // 予約がキャンセル状態、かつ有効行程の場合はキャンセルチャージの合計
            "total_net" => ($this->reserve->is_canceled && $this->enabled) ? $this->total_cancel_charge_net : $this->total_net, // 予約がキャンセル状態、かつ有効行程の場合はキャンセルチャージ(仕入先支払い額)の合計
            "total_gross_profit" => ($this->reserve->is_canceled && $this->enabled) ? $this->total_cancel_charge_profit : $this->total_gross_profit, // 予約がキャンセル状態、かつ有効行程の場合はキャンセルチャージ粗利の合計
            "edit_url" => $editUrl,
            "pdf_url" => $pdfUrl,
            "room_list_url" => $roomListUrl,
            "updated_at" => $this->updated_at->format('Y/m/d H:i:s'),
            "created_at" => $this->created_at->format('Y/m/d'),
            "reserve" => [
                "price_related_change" => $this->reserve->price_related_change ? $this->reserve->price_related_change->change_at->format('Y-m-d H:i:s') : null,
            ]
        ];
    }
}
