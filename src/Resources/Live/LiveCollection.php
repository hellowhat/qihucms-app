<?php

namespace Qihucms\App\Resources\Live;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LiveCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
