<?php

namespace App\Http\Resources\Staff\WebMessage;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 既読情報
 */
class ReadResource extends JsonResource
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
            'read_at' => $this->read_at,
        ];
    }
}
