<?php

namespace App\Http\Resources\Staff\SubjectHotel;

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
            'hotel_name' => $this->hotel_name,
            'ad_gross' => $this->ad_gross,
            'ad_net' => $this->ad_net,
            'note' => $this->note,
            // 以下、リレーション項目
            'supplier' => [
                'name' => $this->supplier->name,
            ],
            'kbn' => [
                'val' => $this->kbns->isNotEmpty() ? $this->kbns[0]->val : null,
            ],
            'room_type' => [
                'val' => $this->room_types->isNotEmpty() ? $this->room_types[0]->val : null,
            ],
            'meal_type' => [
                'val' => $this->meal_types->isNotEmpty() ? $this->meal_types[0]->val : null,
            ]
        ];
    }
}
