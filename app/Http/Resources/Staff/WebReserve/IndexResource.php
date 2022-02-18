<?php

namespace App\Http\Resources\Staff\WebReserve;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Staff\WebOnlineSchedule\ShowResource as WebOnlineScheduleShowResource;

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
        // 各種URL
        $detailUrl = route('staff.web.estimates.reserve.show', [$request->agencyAccount, $this->control_number]);
        $consultationUrl = sprintf("%s?tab=%s", $detailUrl, config('consts.reserves.TAB_CONSULTATION')); // 相談ページタブ

        return [
            "hash_id" => $this->getRouteKey(),
            "detail_url" => route('staff.web.estimates.reserve.show', [$request->agencyAccount, $this->control_number]),
            "control_number" => $this->control_number,
            "name" => $this->name,
            "departure_date" => $this->departure_date,
            "return_date" => $this->return_date,
            "participant_type" => $this->participant_type,
            // 以下、リレーション項目
            "web_reserve_ext" => [
                "id" => $this->web_reserve_ext ? $this->web_reserve_ext->id : null,
                "agency_unread_count" => $this->web_reserve_ext ? $this->web_reserve_ext->agency_unread_count : 0,
                "consultation_url" => $consultationUrl,
                "web_online_schedule" => new WebOnlineScheduleShowResource($this->web_reserve_ext->web_online_schedule),
            ],

            // 以下、リレーション項目
            "manager" => [ // 自社担当
                'name' => $this->manager->name,
                'is_deleted' => $this->manager->trashed()
            ],
            "departure" => [ // 出発地
                'name' => $this->departure->name,
            ],
            "destination" => [ // 目的地
                'name' => $this->destination->name,
            ],
            "travel_type" => [ // 旅行種別
                'val' => $this->travel_types->isNotEmpty() ? $this->travel_types[0]->val : null
            ],
            "status" => [ // 予約ステータス
                'val' => optional($this->status)->val,
            ],
            "application_type" => [ // 申込種別
                'val' => optional($this->application_type)->val,
            ],
            "application_date" => [ // 申込日
                'val' => $this->application_dates->isNotEmpty() ? $this->application_dates[0]->val : null
            ],
            "applicant" => [ // 申込者
                'name' => $this->applicantable ? optional($this->applicantable->userable)->name : null,
                'is_deleted' => $this->applicantable ? optional($this->applicantable->userable)->trashed() : false,
            ],
            "representative" => [ // 代表者
                'state_inc_name' => $this->representatives->isNotEmpty() ? $this->representatives[0]->state_inc_name : null,
                'is_deleted' => $this->representatives->isNotEmpty() ? $this->representatives[0]->user->trashed() : false,
            ]
        ];
    }
}
