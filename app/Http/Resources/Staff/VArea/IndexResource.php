<?php

namespace App\Http\Resources\Staff\VArea;

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
            'uuid' => $this->uuid, 
            'code' => $this->code,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'master' => $this->master,
            'v_direction' => [
                'uuid' => $this->v_direction->uuid,
                'name' => $this->v_direction->name
            ]
        ];
    }
}
