<?php

namespace App\Http\Resources\Staff\Participant;

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
            'id' => $this->id,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'reserve' => [
                'updated_at' => $this->reserve->updated_at->format('Y-m-d H:i:s'),
            ]
        ];
    }
}
