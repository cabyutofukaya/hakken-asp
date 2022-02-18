<?php

namespace App\Http\Resources\Staff\Reserve;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class VAreaResource extends JsonResource
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
            "uuid" => $this->uuid,
            "code" => $this->code,
            "name" => $this->name,
        ];
    }
}
