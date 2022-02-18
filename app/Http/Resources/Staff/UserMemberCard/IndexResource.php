<?php

namespace App\Http\Resources\Staff\UserMemberCard;

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
            'card_name' => $this->card_name,
            'card_number' => $this->card_number,
            'note' => $this->note,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
