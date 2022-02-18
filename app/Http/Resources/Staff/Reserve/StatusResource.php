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
            "val" => $this->val,
            "updated_at" => $this->updated_at,
        ];
    }
}
