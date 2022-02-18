<?php

namespace App\Http\Resources\Staff;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectionResource extends JsonResource
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
            'id' => Hashids::encode($this->id), // IDをハッシュ化
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}
