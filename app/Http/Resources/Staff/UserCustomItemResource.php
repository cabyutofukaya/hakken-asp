<?php

namespace App\Http\Resources\Staff;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCustomItemResource extends JsonResource
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
            'name' => $this->name,
            'user_custom_category_id' => $this->user_custom_category_id,
            'type' => $this->type,
            'key' => $this->key,
            'select_item' => $this->select_item([''=>'---']),
        ];
    }
}
