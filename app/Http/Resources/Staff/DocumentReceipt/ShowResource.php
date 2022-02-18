<?php

namespace App\Http\Resources\Staff\DocumentReceipt;

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
            'description' => $this->description,
            'proviso' => $this->proviso,
            'note' => $this->note,
            'document_common' => $this->document_common,
        ];
    }
}
