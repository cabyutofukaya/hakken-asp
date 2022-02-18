<?php

namespace App\Http\Resources\Staff\UserMileage;

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
        $customValues = $this->v_user_mileage_custom_values->mapWithKeys(function ($item) {
            return [$item['key'] => $item['val']];
        });

        $base = [
            'id' => $this->id,
            'card_number' => $this->card_number,
            'note' => $this->note,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        // カスタム項目とマージ
        return array_merge($base, $customValues->toArray());
    }
}
