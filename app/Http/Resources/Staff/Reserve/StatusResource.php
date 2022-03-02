<?php

namespace App\Http\Resources\Staff\Reserve;

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
            "status" => [ // ステータス
                'val' => $this->status ? $this->status->val : null
            ],
            "updated_at" => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
