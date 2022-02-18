<?php

namespace App\Http\Resources\Staff\WebMessage;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Webメッセージ
 */
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
            'sender' => $this->sender,
            'message' => $this->message,
            'send_at' => $this->send_at,
            'read_at' => $this->read_at,
        ];
    }
}
