<?php

namespace App\Http\Resources\Staff\Participant;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'reserve' => [
                'reserve_itinerary_exists' => $this->reserve->reserve_itinerary_exists ? 1 : 0, // 当該予約に紐づく行程情報があるか否か。参加者を追加した際に行程の更新が必要か否かのメッセージを出す際に使用
            ],
        ];
    }
}
