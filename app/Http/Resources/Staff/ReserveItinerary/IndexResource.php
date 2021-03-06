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
            "total_gross" => $this->total_gross,
            "total_net" => $this->total_net,
            "total_gross_profit" => $this->total_gross_profit,
            "edit_url" => $editUrl,
            "pdf_url" => $pdfUrl,
            "room_list_url" => $roomListUrl,
            "updated_at" => $this->updated_at->format('Y/m/d H:i:s'),
            "created_at" => $this->created_at->format('Y/m/d'),
            "reserve" => [
                "price_related_change" => $this->reserve->price_related_change ? $this->reserve->price_related_change->change_at->format('Y-m-d H:i:s') : null,
                "participant" => [
                    'updated_at' => $this->reserve->latest_all_participant ? $this->reserve->latest_all_participant->updated_at->format('Y-m-d H:i:s') : null,
                ], // 参加者の最終更新日時
            ]
        ];
    }
}
