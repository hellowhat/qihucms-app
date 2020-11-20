<?php

namespace Qihucms\App\Resources\Ad;

use App\Services\PhotoService;
use Illuminate\Http\Resources\Json\JsonResource;

class Ad extends JsonResource
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
            'ad_category_id' => $this->ad_category_id,
            'img_src' => (new PhotoService())->getImgUrl($this->img_src),
            'alt' => $this->alt,
            'title' => $this->title,
            'url' => $this->url
        ];
    }
}
