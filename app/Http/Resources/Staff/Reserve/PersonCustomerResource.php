<?php

namespace App\Http\Resources\Staff\Reserve;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonCustomerResource extends JsonResource
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
            "is_deleted" => $this->trashed(),
            // リレーション
            'userable' => [
                "name" => $this->userable->name,
                "name_kana" => $this->userable->name_kana,
                "name_roman" => $this->userable->name_roman,
                "sex_label" => $this->userable->sex_label,
                "age_calc" => $this->userable->age_calc,
                'passport_number' => $this->userable->passport_number,
                "passport_expiration_date" => $this->userable->passport_expiration_date,
                "mobile_phone" => $this->userable->mobile_phone,
                "user_ext" => [
                    "age_kbn_label" => $this->userable->user_ext ? $this->userable->user_ext->age_kbn_label : null,
                ]
            ]
        ];
    }
}
