<?php

namespace App\Http\Resources\Staff\Document;

use Hashids;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 帳票設定の領収書
 */
class DocumentReceiptResource extends JsonResource
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
            'id' => $this->getRouteKey(), // IDをハッシュ化
            'name' => $this->name,
            'description' => $this->description,
            'undelete_item' => $this->undelete_item,
        ];
    }
}
