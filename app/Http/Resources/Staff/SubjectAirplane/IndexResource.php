<?php

namespace App\Http\Resources\Staff\SubjectAirplane;

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
            'name' => $this->name,
            'booking_class' => $this->booking_class,
            'ad_gross' => $this->ad_gross,
            'ad_net' => $this->ad_net,
            'supplier' => $this->supplier->name,
            'note' => $this->note,
            // 以下、リレーション項目
            'departure' => [
                'name' => $this->departure->name,
            ],
            'destination' => [
                'name' => $this->destination->name,
            ],
            'airline' => [
                'val' => $this->airlines->isNotEmpty() ? $this->airlines[0]->val : null,
            ],
        ];
    }
}
