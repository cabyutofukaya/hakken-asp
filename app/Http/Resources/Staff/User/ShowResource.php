<?php

namespace App\Http\Resources\Staff\User;

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
            'user_number' => $this->user_number,
            'name' => $this->name,
            'name_kana' => $this->name_kana,
            'name_roman' => $this->name_roman,
            'sex'=> $this->sex,
            'age' => $this->age,
            'age_kbn' => $this->age_kbn,
            'mobile_phone' => $this->mobile_phone,
            'tel' => $this->tel,
            'fax' => $this->fax,
            'emergency_contact' => $this->emergency_contact,
            'emergency_contact_column' => $this->emergency_contact_column,
            'email' => $this->email,
            'zip_code' => $this->zip_code,
            'prefecture_code' => $this->prefecture_code,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'passport_number' => $this->passport_number,
            'passport_issue_date' => $this->passport_issue_date,
            'passport_expiration_date' => $this->passport_expiration_date,
            'passport_issue_country_code' => $this->passport_issue_country_code,
            'citizenship_code' => $this->citizenship_code,
            'workspace_name' => $this->workspace_name,
            'workspace_address' => $this->workspace_address,
            'workspace_tel' => $this->workspace_tel,
            'workspace_note' => $this->workspace_note,
            'dm' => $this->dm,
            'note' => $this->note,
            'status' => $this->status,
        ];
    }
}
