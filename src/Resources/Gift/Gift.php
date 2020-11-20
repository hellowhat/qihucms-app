<?php

namespace Qihucms\App\Resources\Gift;

use App\Services\PhotoService;
use Illuminate\Http\Resources\Json\JsonResource;

class Gift extends JsonResource
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
            'name' => $this->name,
            'thumbnail' => (new PhotoService())->getImgUrl($this->thumbnail),
            'pay_balance' => $this->pay_balance,
            'pay_jewel' => $this->pay_jewel,
            'pay_integral' => $this->pay_integral,
            'unit' => $this->unit
        ];
    }
}
