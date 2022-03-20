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
                'reserve_itinerary_exists' => $this->reserve->reserve_itinerary_exists ? 1 : 0, // 当該予約に紐づく行程情報があるか否か。行程の更新が必要か否かのメッセージを出す際に使用
                'updated_at' => $this->reserve->updated_at->format('Y-m-d H:i:s'),
            ]
        ];
    }
}
