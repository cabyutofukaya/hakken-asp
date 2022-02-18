<?php

namespace App\Http\Resources\Staff\WebReserve;

use Illuminate\Http\Resources\Json\JsonResource;

// 利用履歴一覧用
class HistoryIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $rowNumber = null; // 見積or予約番号
        $rowStatus = null; // 見積or予約ステータス
        $detailUrl = null; // 詳細URL

        if ($this->application_step === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積
            $rowNumber = $this->estimate_number;
            $rowStatus = $this->estimate_statuses->isNotEmpty() ? $this->estimate_statuses[0]->val : null;
            $detailUrl = route('staff.asp.estimates.normal.show', [$request->agencyAccount, $rowNumber]);

        } elseif ($this->application_step === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
            $rowNumber = $this->control_number;
            $rowStatus = $this->statuses->isNotEmpty() ? $this->statuses[0]->val : null;
            $detailUrl = route('staff.asp.estimates.reserve.show', [$request->agencyAccount, $rowNumber]);
        }

        return [
            'application_step' => $this->application_step,
            'row_number' => $rowNumber, // 見積と予約で異なる
            'name' => $this->name,
            'departure_date' => $this->departure_date,
            'representative_name' => $this->representative_name,
            'headcount' => $this->headcount,
            'sum_gross' => $this->sum_gross,
            'row_status' => $rowStatus, // 見積と予約で異なる
            'detail_url' => $detailUrl,
            // リレーション
            'destination' => [
                'name' => $this->destination->name,
            ]
        ];
    }
}
