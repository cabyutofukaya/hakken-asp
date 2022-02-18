<?php

namespace App\Http\Resources\Staff\BusinessUserManager;

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
            'user_number' => $this->user_number,
            'name' => $this->name,
            'name_roman' => $this->name_roman,
            'sex' => $this->sex,
            'department_name' => $this->department_name,
            'email' => $this->email,
            'tel' => $this->tel,
            'dm' => $this->dm,
            'dm_label' => $this->dm_label,
            'note' => $this->note,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
