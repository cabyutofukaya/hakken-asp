<?php

namespace App\Http\Resources\Staff\Staff;

use Hashids;
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
            'agency_id' => $this->agency_id,
            'account' => $this->account,
            'name' => $this->name,
            'agency_role_id' => $this->agency_role_id,
            'email' => $this->email,
            'status' => $this->status,
            'agency_role' => [
                'name' => $this->agency_role->name,
            ],
            'shozoku' => [
                'val' => $this->shozokus->isNotEmpty() ? $this->shozokus[0]->val : null,
            ]
        ];
    }
}
