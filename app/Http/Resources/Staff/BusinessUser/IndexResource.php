<?php

namespace App\Http\Resources\Staff\BusinessUser;

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
            'user_number' => $this->user_number,
            'name' => $this->name,
            'tel' => $this->tel,
            'address' => $this->address1 . $this->address2,
            'status' => $this->status,
            'status_label' => $this->status_label,
            // 以下、リレーション項目
            'kbn' => [
                'val' => $this->kbns->isNotEmpty() ? $this->kbns[0]->val : null
            ],
            'prefecture' => [
                'name' => $this->prefecture->name
            ],
        ];
    }
}
