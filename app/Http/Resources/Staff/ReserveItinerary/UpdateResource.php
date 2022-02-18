<?php

namespace App\Http\Resources\Staff\ReserveItinerary;

use Illuminate\Http\Resources\Json\JsonResource;

class UpdateResource extends JsonResource
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
            "control_number" => $this->control_number,
            "enabled" => $this->enabled,
            "updated_at" => $this->updated_at->format('Y/m/d H:i:s'),
        ];
    }
}
