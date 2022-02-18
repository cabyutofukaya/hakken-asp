<?php

namespace App\Http\Resources\Staff\AgencyNotification;

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
            'id' => $this->id,
            'content' => $this->content,
            'regist_date' => $this->regist_date,
            'read_at' => $this->read_at,
        ];
    }
}
