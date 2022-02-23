<?php

namespace App\Http\Resources\Staff\WebEstimate;

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
        // 詳細URL。リクエスト状態の判定にはapplication_stepを使うこと。
        $detailUrl = '';
        $consultationUrl = "";
        if ($this->application_step == config('consts.reserves.APPLICATION_STEP_CONSULT')) { // オンライン相談
            $detailUrl = route('staff.web.estimates.normal.request', [$request->agencyAccount, $this->request_number ?? ""]);
        } elseif ($this->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積
            $detailUrl = route('staff.web.estimates.normal.show', [$request->agencyAccount, $this->estimate_number ?? ""]);
            $consultationUrl = sprintf("%s?tab=%s", $detailUrl, config('consts.reserves.TAB_CONSULTATION')); // 相談ページタブ
        }

        return [
            "hash_id" => $this->getRouteKey(),
            "invalid" => $this->web_reserve_ext ? ($this->web_reserve_ext->rejection_at || optional($this->web_reserve_ext->web_consult)->cancel_at) : false,// 無効行(自体済or取り消し済)の場合はtrue
            "detail_url" => $detailUrl,
            "record_number" => $this->record_number,
            "name" => $this->name,
            "departure_date" => $this->departure_date,
            "return_date" => $this->return_date,
            "participant_type" => $this->participant_type,
            // "application_step" => $this->application_step,
            // 以下、リレーション項目
            "web_reserve_ext" => [
                "id" => $this->web_reserve_ext ? $this->web_reserve_ext->id : null,
                "agency_unread_count" => $this->web_reserve_ext ? $this->web_reserve_ext->agency_unread_count : 0,
                "estimate_status" => $this->web_reserve_ext ? $this->web_reserve_ext->estimate_status : null,
                "estimate_status_label" => $this->web_reserve_ext ? $this->web_reserve_ext->estimate_status_label :null,
                "rejection_at" => $this->web_reserve_ext->rejection_at,
                "consultation_url" => $consultationUrl,
                "web_online_schedule" => new WebOnlineScheduleShowResource($this->web_reserve_ext->web_online_schedule),
            ],
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
                'val' => optional($this->travel_type)->val
            ],
            "status" => [ // 見積ステータス
                'val' => optional($this->estimate_status)->val,
            ],
            "application_type" => [ // 申込種別
                'val' => optional($this->application_type)->val,
            ],
            "application_date" => [ // 申込日
                'val' => optional($this->application_date)->val
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
