<?php

namespace App\Http\Resources\Staff\City;

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
        return [
            'id' => $this->getRouteKey(),
            'code' => $this->code,
            'name' => $this->name,
            //　以下、リレーション
            'v_area' => [
                'name' => $this->v_area->name,
            ]
        ];
    }
}
