<?php

namespace App\Http\Resources\Staff\DocumentCommon;

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
            'code' => $this->code,
            'name' => $this->name,
            'setting' => $this->setting,
            'company_name' => $this->company_name,
            'supplement1' => $this->supplement1,
            'supplement2' => $this->supplement2,
            'zip_code' => $this->zip_code,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'tel' => $this->tel,
            'fax' => $this->fax,
        ];
    }
}
