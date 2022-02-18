<?php

namespace App\Http\Resources\Staff\EstimateItinerary;

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
            "control_number" => $this->control_number,
            "enabled" => $this->enabled,
            "note" => mb_strimwidth($this->note, 0, 30, "..."),
            "sum_gross" => $this->sum_gross,
            "sum_net" => $this->sum_net,
            "sum_gross_profit" => $this->sum_gross_profit,
            // "pdf_url" => route('staff.asp.estimates.normal.itinerary.pdf', [$this->reserve->agency->account, $this->reserve->estimate_number, $this->estimate_number]),
            // "room_list_url" => $this->reserve_participant_hotel_prices->where('valid', true)->count() > 0 ? route('staff.asp.estimates.normal.itinerary_roominglist.pdf', [$this->reserve->agency->account, $this->reserve->estimate_number, $this->estimate_number]) : null, // 有効な宿泊科目情報がある場合はURLを返す
            "updated_at" => $this->updated_at->format('Y/m/d H:i:s'),
            "created_at" => $this->created_at->format('Y/m/d')
        ];
    }
}
