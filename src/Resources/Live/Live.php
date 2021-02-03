<?php

namespace Qihucms\App\Resources\Live;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\User;
use App\Http\Resources\Goods\Goods as GoodResources;
use App\Models\Goods;
use Qihucms\Live\Services\TencentLiveService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

class Live extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Exception
     */
    public function toArray($request)
    {
        $liveService = new TencentLiveService();
        if($this->status == 1){
            $playUrl = $liveService->PlayUrl('room-' . $this->user_id);
        }else{
            $playUrl = $this->hls;
        }

        if (auth()->check()) {
            $user = auth()->id();
        }else{
            $user = $request->header('Auth-user-id',0);
        }

        //红包信息
        $red_ids = Redis::smembers('live-red-' . $this->user_id);
        $red_data = [];
        if (count($red_ids) > 0) {
            foreach ($red_ids as $red) {
                $red_data[] = Redis::hgetall('live-red-' . $this->user_id . '-' . $red);
                if ($user && Redis::sismember('live-red-' . $this->user_id . '-' . $red . '_get', $user)) {
                    $red_data[count($red_data) - 1]['get'] = true;
                }
            }
            $red_data = array_reverse($red_data);
        }

        return [
            'user_id' => $this->user_id,
            'link' => route('live.wap.room',['id'=>$this->user_id]),
            'user' => new User($this->user),
            'hls' => $playUrl,
            'backs' => $this->backs,
            'product' => $this->product ? new GoodResources(Goods::find($this->product)) : null,
            'category_id' => $this->category_id,
            'category' => $this->category->title,
            'title' => $this->title,
            'cover' => Storage::url($this->cover.'?imageMogr2/auto-orient/blur/50x50'),
            'peoples' => cache('live-room-online-'.$this->user_id,0),
            'ordering' => cache('live-gift-ordering-' . $this->user_id,null),
            'redbag' => $red_data,
            'status' => $this->status
        ];
    }
}
