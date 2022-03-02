<?php

namespace App\Http\Resources\Staff\WebEstimate;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
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
            "updated_at" => $this->updated_at->format('Y-m-d H:i:s'),
            "status" => [ // ステータス
                'val' => $this->estimate_status ? $this->estimate_status->val : null
            ],
        ];
    }
}
