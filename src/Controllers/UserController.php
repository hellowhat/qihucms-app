<?php

namespace Qihucms\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShortVideo;
use App\Models\UserCount;
use App\Models\UserFollow;
use App\Repositories\UserRepository;
use App\Services\MakeSpreadCodeService;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Auth;
use App\Services\PhotoService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\ToolsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    public function info(Request $request)
    {
        $user = $request->user();

        if(empty($user->api_token)){
            $user->api_token = Str::random(60);
            $user->save();
        }
        $user['apiToken'] = $user->api_token;

        $user['account'] = $user->account;

        // 推广码
        $user['spreadCode'] = (new MakeSpreadCodeService())->encode($user['id']);
        $user['spreadQrcode'] = (new QrCodeService())->userQrCode($user['id'],$user->avatar);
        $user->avatar = (new PhotoService())->getImgUrl($user->avatar,120,120);
        // 更新会员作品数、关注数、粉丝数
        $video_count = ShortVideo::where('user_id', Auth::id())->count();
        $follow_count = UserFollow::where('user_id', Auth::id())->where('status', '>', 0)->count();
        $fans_count = UserFollow::where('to_user_id', Auth::id())->where('status', '>', 0)->count();
        if ($user['user_count']['video'] != $video_count) {
            UserCount::where('user_id', $user['id'])->update(['video' => $video_count]);
        }
        if ($user['user_count']['follower'] != $follow_count) {
            UserCount::where('user_id', $user['id'])->update(['follower' => $follow_count]);
        }
        if ($user['user_count']['fans'] != $fans_count) {
            UserCount::where('user_id', $user['id'])->update(['fans' => $fans_count]);
        }

        $user['notices'] = $user->unreadNotifications->count();
        return response()->json($user);
    }

    public function notices(Request $request)
    {
        return $request->user()->unreadNotifications;
    }

    public function updateFollowApi(Request $request)
    {
        $result = $this->user->follow(Auth::id(), $request->get('id'));
        return $this->successJson('操作成功', ['is_follow' => $result]);
    }

    public function homepage(Request $request,$id)
    {
        $user = $this->user->findById($id);
        if($user){
            if(isset($user->mall->user_id) && $user->mall->status == 1){
                $user['mall'] = $user->mall;
            }
            $user['count'] = $user->user_count;
            // 设置 banner 图片地址
            if (empty($user['banner'])) {
                $user['banner'] = Cache::get('config_default_homepage_banner');
            }
            if (!empty($user['banner'])) {
                $user['banner'] = Storage::url($user['banner']);
            }

            $user['avatar'] = (new PhotoService())->getImgUrl($user['avatar'],120,120);
            $toolsService = new ToolsService();
            $user['age'] = $toolsService->getAge($user['birthday']);
            $user['constellation'] = $toolsService->getConstellation($user['birthday']);
            $user['city'] = $toolsService->getCity($user['city']);

            $auth_id = $request->header('Auth-user-id',0);
            if($auth_id){
                $user['is_follow'] = $this->user->isFollow($request->header('Auth-user-id'),$id);
            }else{
                $user['is_follow'] = false;
            }

            $title = Cache::get('config_share_homepage_title', $user['nickname'] . '的主页');
            $title = str_replace('${nickname}', $user['nickname'], $title);
            $title = str_replace('${user_id}', $user['id'], $title);

            $desc = Cache::get('config_share_homepage_desc', '');
            $desc = str_replace('${nickname}', $user['nickname'], $desc);
            $desc = str_replace('${user_id}', $user['id'], $desc);

            switch (Cache::get('config_share_homepage_img_type')) {
                case 'banner':
                    $img = $user['banner'];
                    break;
                case 'avatar':
                    $img = $user['avatar'];
                    break;
                default:
                    $img = Storage::url(Cache::get('config_share_homepage_custom_img'));
            }

            $user['share'] = [
                'title' => $title,
                'desc' => $desc,
                'img' => $img,
                'url' => route('sharing_page', ['module' => 'homepage', 'id' => $user['id'], 'uid' => $auth_id])
            ];

            $qrCode = new QrCodeService();
            $url = route('sharing_page', ['module' => 'homepage', 'id' => $user['id'], 'uid' => $auth_id]);
            $user['qrcode'] = 'data:image/png;base64,'.base64_encode($qrCode->urlQrCode($url));

            $user['is_living'] = Schema::hasTable('lives') ? DB::table('lives')->where('user_id',$user['id'])->value('status') : 0;

            unset($user['mobile'],$user['last_login_ip'],$user['last_login_time'],$user['created_at'],$user['updated_at'],$user['email']);
            return response()->json($user);
        }else{
            //找不到用户
            return response()->json([]);
        }
    }
}