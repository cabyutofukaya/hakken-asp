<?php

namespace App\Http\Resources\Staff\Participant;

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
            'age' => $this->age_calc, // 生年月日から年齢を計算
            'note' => $this->note,
            'representative' => $this->representative,
            'cancel' => $this->cancel,
            //
            'name' => $this->name,
            'state_inc_name' => $this->state_inc_name,
            'name_kana' => $this->name_kana,
            'name_roman' => $this->name_roman,
            'birthday_y' => $this->birthday_y,
            'birthday_m' => $this->birthday_m,
            'birthday_d' => $this->birthday_d,
            "age_kbn" => $this->age_kbn,
            "age_kbn_label" => $this->age_kbn_label,
            "sex" => $this->sex,
            "sex_label" => $this->sex_label,
            'passport_number' => $this->passport_number,
            'passport_issue_date' => $this->passport_issue_date,
            'citizenship_code' => $this->citizenship_code,
            "passport_expiration_date" => $this->passport_expiration_date,
            'passport_issue_country_code' => $this->passport_issue_country_code,
            "mobile_phone" => $this->mobile_phone,
            // 以下、リレーション項目
            "user" => [
                'user_number' => $this->user->user_number,
                'is_deleted' => $this->user->trashed(), // 削除済みユーザーか否か
                'updated_at' => $this->user->updated_at->format('Y-m-d H:i:s'),
            ],
        ];
    }
}
