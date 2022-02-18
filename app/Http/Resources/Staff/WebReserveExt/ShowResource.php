<?php

namespace App\Http\Resources\Staff\WebReserveExt;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // 必要に応じて返却カラムを追加していく
        return [
            "id" => $this->id,
            "rejection_at" => $this->rejection_at,
            "consent_at" => $this->consent_at,
            "updated_at" => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
