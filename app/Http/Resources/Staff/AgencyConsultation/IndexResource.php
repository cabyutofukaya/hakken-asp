<?php

namespace App\Http\Resources\Staff\AgencyConsultation;

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
        // カスタム項目
        $customValues = $this->v_agency_consultation_custom_values->mapWithKeys(function ($item) {
            return [$item['key'] => $item['val']];
        });

        // リンクURL
        $reserveUrl = '';
        if ($this->taxonomy == config('consts.agency_consultations.TAXONOMY_RESERVE')) { // 相談種別が「予約」
            // 予約状態によってURLを出し分け(見積or予約)
            if ($this->reserve->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積
                $reserveUrl = route('staff.asp.estimates.normal.show', [$request->agencyAccount, $this->reserve->estimate_number]) . "?tab=" . config('consts.reserves.TAB_CONSULTATION') . "&consultation_number=" . $this->control_number;
            } elseif ($this->reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
                $reserveUrl = route('staff.asp.estimates.reserve.show', [$request->agencyAccount, $this->reserve->control_number]) . "?tab=" . config('consts.reserves.TAB_CONSULTATION') . "&consultation_number=" . $this->control_number;
            }
        }

        $base = [
            'id' => $this->id,
            'reserve_url' => $reserveUrl,
            'taxonomy' => $this->taxonomy,
            'control_number' => $this->control_number,
            'title' => $this->title,
            'reception_date' => $this->reception_date,
            'kind' => $this->kind,
            'kind_label' => $this->kind_label,
            'deadline' => $this->deadline,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'contents' => $this->contents,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            // 以下、リレーション項目
            "reserve" => [ // 予約/見積
                // 'control_number'=> $this->reserve->control_number,
                // 'estimate_number'=> $this->reserve->estimate_number,
                "record_number" => $this->reserve->record_number,
                'application_step'=> $this->reserve->application_step,
                "applicant" => [ // 申込者
                    'name' => $this->reserve->applicantable ? $this->reserve->applicantable->name : null,
                    'is_deleted' => $this->reserve->applicantable ? $this->reserve->applicantable->trashed() : false,
                ],
            ],
            "manager" => [ // 自社担当
                'id' => $this->manager->id,
                'name' => $this->manager->name,
                'is_deleted' => $this->manager->trashed()
            ],
        ];

        // カスタム項目とマージ
        return array_merge($base, $customValues->toArray());
    }
}
