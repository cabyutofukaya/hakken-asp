<?php

namespace App\Http\Resources\Staff\WebModelcourse;

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
            'show' => $this->show,
            'course_no' => $this->course_no,
            'name' => $this->name,
            'price_per_ad' => is_numeric($this->price_per_ad) ? number_format($this->price_per_ad) : $this->price_per_ad,
            'price_per_ch' => is_numeric($this->price_per_ch) ? number_format($this->price_per_ch) : $this->price_per_ch,
            'price_per_inf' => is_numeric($this->price_per_inf) ? number_format($this->price_per_inf) : $this->price_per_inf,
            'preview_url' => env('MIX_OPEN_MODE') === 'grand-open' ? get_modelcourse_previewurl($request->agencyAccount, $this->course_no) : '', // プレビューURLはグランドオープン時に有効化
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            // 以下リレーション
            'departure' => [
                'name' => $this->departure->name,
            ],
            'destination' => [
                'name' => $this->destination->name,
            ],
            'author' => [
                'name' => $this->author->name,
                'is_deleted' => $this->author->trashed()
            ],
        ];

    }
}
