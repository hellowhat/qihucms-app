<?php

namespace Qihucms\App\Resources\Video;

use App\Http\Resources\User\User;
use App\Models\UserFollow;
use App\Models\UserLikeVideo;
use App\Models\User as UserModel;
use App\Services\PhotoService;
use App\Services\ToolsService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class ShortVideo extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        // 封面
        if (empty($this->cover)) {
            $cover = Cache::get('config_default_short_video_cover');
        } else {
            $cover = $this->cover;
        }
        $cover = (new PhotoService())->getImgUrl($cover, 750, 1334);

        // 标签
        $tags = explode(',', $this->tags);
        if ($tags) {
            foreach ($tags as $k => $v) {
                if ($v == '') unset($tags[$k]);
            }
        }

        // 广告
        $remark = $this->remark;
        if (is_array($remark)) {
            // 如果是推广产品
            if (isset($remark['ad']['goods_id'])) {
                $remark['ad']['goods_thumbnail'] = $remark['ad']['ico'] = (new PhotoService())->getImgUrl($remark['ad']['goods_thumbnail'], 66, 66);
            } else {
                if (isset($remark['ad']['ico'])) {
                    $remark['ad']['ico'] = asset('asset/ad_ico/' . $remark['ad']['ico'] . '.png');
                }
            }
            if (isset($remark['ad']['link'])) {
                $remark['ad']['link'] = route('jump.goto', ['vid' => $this->id]);
            } else {
                $remark['ad']['link'] = '#';
            }
        }

        $src = $this->src;

        if (auth()->check()) {
            $user = auth()->user();
        }else{
            $user = UserModel::find($request->header('Auth-user-id',0));
        }

        // 如果VIP视频作者查看，转换成免费视频
        if (isset($user['id']) && ($user['id'] == $this->user_id || $user['vip_rank'] > 0)) {
            $price = 0;
        } else {
            $price = $this->price;
        }

        // VIP视频规替换资源址
        if ($price > 0) {
            $src = Cache::get('config_default_paid_video');
        }

        $toolsService = new ToolsService();

        return [
            'id' => $this->id,
            'user' => new User($this->user),
            'desc' => $this->desc,
            'cover' => $cover,
            'src' => Storage::url($src),
            'tags' => $tags,
            'city' => $this->city,
            'link' => $this->link,
            'exif' => $this->exif,
            'heat' => $this->heat,
            'look' => $toolsService->format_number($this->look),
            'like' => $toolsService->format_number($this->like),
            'share' => $this->share,
            'comment' => $this->comment,
            'price' => $this->price,
            'remark' => $remark,
            'is_like' => isset($user['id']) ? UserLikeVideo::where('user_id', $user['id'])->where('short_video_id', $this->id)->where('status', 1)->exists() : false
        ];
    }
}
