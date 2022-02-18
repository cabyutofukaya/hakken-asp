<?php

namespace App\Http\Resources\Staff\Bank;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class TenpoResource extends JsonResource
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
            'kinyu_code' => $this->kinyu_code ?? "",
            'kinyu_kana' => $this->kinyu_kana ?? "",
            'kinyu_name' => $this->kinyu_name ?? "",
            'tenpo_code' => $this->tenpo_code ?? "",
            'tenpo_kana' => $this->tenpo_kana ?? "",
            'tenpo_name' => $this->tenpo_name ?? "",
        ];
    }
}
