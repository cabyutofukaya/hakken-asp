<?php

namespace App\Http\Resources\Staff\AgencyWithdrawal;

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
        $customValues = $this->v_agency_withdrawal_custom_values->mapWithKeys(function ($item) {
            return [$item['key'] => $item['val']];
        });
        
        $base = [
            'id' => $this->id,
            'amount' => $this->amount,
            'manager_id' => $this->manager_id,
            'note' => $this->note,
            'record_date' => $this->record_date,
            'withdrawal_date' => $this->withdrawal_date,
            // 'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        // カスタム項目とマージ
        return array_merge($base, $customValues->toArray());
    }
}
