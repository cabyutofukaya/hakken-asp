<?php

namespace App\Http\Resources\Staff\VDirection;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
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
            'uuid' => $this->uuid, 
            "code" => $this->code,
            "name" => $this->name,
        ];
    }
}
