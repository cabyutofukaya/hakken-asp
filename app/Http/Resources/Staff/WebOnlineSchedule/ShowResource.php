<?php

namespace App\Http\Resources\Staff\WebOnlineSchedule;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
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
            'reserve_id' => $this->reserve_id,
            'web_reserve_ext_id' => $this->web_reserve_ext_id,
            'consult_date' => $this->consult_date,
            'requester' => $this->requester,
            'request_status' => $this->request_status,
            'zoom_start_url' => $this->zoom_start_url,
            // 'created_at' => $this->created_at,
        ];
    }
}
