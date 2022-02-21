<?php

namespace App\Http\Resources\Staff\WebMessageHistory;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Webメッセージ履歴一覧
 */
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
        // メッセージページのURL
        $infoUrl = '';
        if (optional($this->reserve)->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 予約前
            $infoUrl = route('staff.web.estimates.normal.show', [
                $request->agencyAccount, optional($this->reserve)->estimate_number ?? ''
            ]) . "?tab=" . config('consts.reserves.TAB_CONSULTATION');
        } elseif (optional($this->reserve)->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
            $infoUrl = route('staff.web.estimates.reserve.show', [
                $request->agencyAccount, optional($this->reserve)->control_number ?? ''
            ]) . "?tab=" . config('consts.reserves.TAB_CONSULTATION');
        }

        // ステータス。見積or予約で取得リレーションを切り替え
        return [
            'id' => $this->id,
            'info_url' => $infoUrl,
            'reserve_status' => $this->reserve_status,
            'last_received_at' => $this->last_received_at,
            // リレーション
            'reserve' => [
                'record_number' => $this->reserve->record_number ?? null, // 見積/予約/依頼番号
                // 申込者
                'applicant' => [
                    'name' => $this->reserve->applicantable->userable->name ?? null,
                ],
                "application_date" => [ // 申込日
                    'val' => $this->reserve->application_dates->isNotEmpty() ? $this->reserve->application_dates[0]->val : null
                ],
                // 自社担当
                "manager" => [
                    'name' => $this->reserve->manager->name ?? null,
                    'is_deleted' => $this->reserve->manager ? $this->reserve->manager->trashed() : false,
                ],
                'web_reserve_ext' => [
                    "estimate_status" => $this->reserve ? optional($this->reserve->web_reserve_ext)->estimate_status : null,
                    "estimate_status_label" => $this->reserve ? optional($this->reserve->web_reserve_ext)->estimate_status_label :null,
                ]
            ]
        ];
    }
}
