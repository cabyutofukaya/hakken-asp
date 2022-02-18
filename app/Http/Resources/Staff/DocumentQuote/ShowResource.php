<?php

namespace App\Http\Resources\Staff\DocumentQuote;

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
            'title' => $this->title,
            'document_common_id' => $this->document_common_id,
            'name' => $this->name,
            'seal' => $this->seal,
            'seal_number' => $this->seal_number,
            'seal_items' => $this->seal_items,
            'seal_wording' => $this->seal_wording,
            'information' => $this->information,
            'note' => $this->note,
            'setting' => $this->setting,
            'document_common' => $this->document_common,
        ];
    }
}
