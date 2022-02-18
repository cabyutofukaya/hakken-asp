<?php

namespace App\Http\Resources\Staff\WebReserve;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

// 法人顧客 担当者検索結果用
class BusinessCustomerResource extends JsonResource
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
            "user_number" => $this->user_number,
            "name" => $this->name,
            'department_name' => $this->department_name,
            "email" => $this->email,
            "tel" => $this->tel,
            "is_deleted" => $this->trashed(),
            // 以下、リレーション項目
            "business_user" => [ // 法人顧客
                "user_number" => $this->business_user->user_number,
                "name" => $this->business_user->name,
                "prefecture_name" => $this->business_user->prefecture->name,
                "is_deleted" => $this->business_user->trashed(),
                'kbn' => [
                    'val' => $this->business_user->kbns->isNotEmpty() ? $this->business_user->kbns[0]->val : null
                ],
            ],
        ];
    }
}
