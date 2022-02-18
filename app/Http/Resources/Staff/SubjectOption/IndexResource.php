<?php

namespace App\Http\Resources\Staff\SubjectOption;

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
            'ad_gross' => $this->ad_gross,
            'ad_net' => $this->ad_net,
            'note' => $this->note,
            // 以下、リレーション項目
            'kbn' => [
                'val' => $this->kbns->isNotEmpty() ? $this->kbns[0]->val : null,
            ],
            'supplier' => [
                'name' => $this->supplier->name,
            ],
            // city廃止
            // 'city' => [
            //     'name' => "{$this->city->code}{$this->city->name}",
            // ]
        ];
    }
}
