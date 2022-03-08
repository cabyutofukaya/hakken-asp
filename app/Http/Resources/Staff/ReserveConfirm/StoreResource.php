<?php

namespace App\Http\Resources\Staff\ReserveConfirm;

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
            'confirm_number' => $this->confirm_number,
            'pdf' => [
                'id' => $this->pdf ? $this->pdf->getRouteKey() : null
            ],
            'reserve' => [
                'updated_at' => $this->reserve->updated_at->format('Y-m-d H:i:s'),
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
