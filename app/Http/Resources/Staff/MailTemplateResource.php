<?php

namespace App\Http\Resources\Staff;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

class MailTemplateResource extends JsonResource
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
            'id' => Hashids::encode($this->id), // IDをハッシュ化
            'name' => $this->name,
            'description' => $this->description,
            'subject' => $this->subject,
            'body' => $this->body,
            'setting' => $this->setting,
        ];
    }
}
