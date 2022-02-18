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
        $controlNumber = null; // 予約or見積番号
        if ($this->reserve->application_step == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $controlNumber = $this->reserve->estimate_number;
        } elseif ($this->reserve->application_step == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $controlNumber = $this->reserve->control_number;
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
            "estimate_number" => $this->estimate_number,
            "enabled" => $this->enabled,
            "note" => mb_strimwidth($this->note, 0, 30, "..."),
            "sum_gross" => $this->sum_gross,
            "sum_net" => $this->sum_net,
            "sum_gross_profit" => $this->sum_gross_profit,
            "pdf_url" => $pdfUrl,
            "room_list_url" => $roomListUrl,
            "updated_at" => $this->updated_at->format('Y/m/d H:i:s'),
            "created_at" => $this->created_at->format('Y/m/d')
        ];
    }
}
