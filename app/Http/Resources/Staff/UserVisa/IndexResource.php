<?php

namespace App\Http\Resources\Staff\UserVisa;

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
            'number' => $this->number,
            'country_code' => $this->country_code,
            'kind' => $this->kind,
            'issue_place_code' => $this->issue_place_code,
            'issue_date' => $this->issue_date,
            'expiration_date' => $this->expiration_date,
            'note' => $this->note,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            // 以下、リレーション項目
            'country' => [
                'name' => $this->country->name,
            ],
            'issue_place' => [
                'name' => $this->issue_place->name,
            ]
        ];
    }
}
