<?php

namespace Qihucms\App\Resources\GoodsCategory;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodsCategory extends JsonResource
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
            'thumbnail' => Storage::url($this->thumbnail),
            'sort' => $this->sort
        ];
    }
}
