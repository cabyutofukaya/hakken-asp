<?php

namespace App\Http\Resources\Staff\User;

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
            'user_number' => $this->user_number,
            'status' => $this->status,
            'status_label' => $this->status_label,
            // リレーション
            'userable' => [
                'name' => $this->userable->name,
                'name_kana' => $this->userable->name_kana,
                'name_roman' => $this->userable->name_roman,
                'mobile_phone' => $this->userable->mobile_phone,
                'email' => $this->userable->email,
                ],
        ];
    }
}
