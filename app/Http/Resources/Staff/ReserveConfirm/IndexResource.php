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
        return [
            'id' => $this->id,
            'confirm_number' => $this->confirm_number,
            'title' => data_get($this, "document_setting.title"),
            'issue_date' => $this->issue_date,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'document_address' => $this->document_address,
            'amount_total' => $this->amount_total,
            // 削除不可テンプレート場合はtrue
            'is_nondelete' => in_array($this->document_quote->code, config('consts.reserve_confirms.NO_ADD_OR_DELETE_CODE_LIST'), true),
            'pdf' => [
                'id' => $this->pdf ? $this->pdf->getRouteKey() : null
            ],
            'reserve_itinerary' => [
                'control_number' => $this->reserve_itinerary ? $this->reserve_itinerary->control_number : null,
            ]
        ];
    }
}
