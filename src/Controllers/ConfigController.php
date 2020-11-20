<?php

namespace Qihucms\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\GoodsCategory;
use Qihucms\App\Resources\Ad\AdCollection;
use Qihucms\App\Models\AppMenu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use EasyWeChat\Factory;
use Qihucms\App\Resources\GoodsCategory\GoodsCategoryCollection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function env(Request $request)
    {
        $data = [];
        //小程序open信息
        if($request->filled('code')){
            $app = Factory::miniProgram([
                'app_id' => config('qihu.wechat_mini_appid'),
                'secret' => config('qihu.wechat_mini_secret')
            ]);
            $data['open'] = $app->auth->session($request->query('code'));
        }else{
            $data['open'] = [];
        }
        //个人中心菜单
        $data['menus'] = AppMenu::orderBy('line','asc')->get();
        //广告
        $data['ads'] = Ad::whereDate('start_time', '<=', now())
            ->whereDate('end_time', '>', now())
            ->latest()
            ->get();
        $data['ads'] = new AdCollection($data['ads']);
        //直播间公告
        $data['live_notice'] = [];
        if(!empty(cache('config_live_notice'))){
            foreach(explode(PHP_EOL, cache('config_live_notice')) as $notice){
                $data['live_notice'][] = [
                    'type' => 'notice',
                    'content' => trim($notice)
                ];
            }
        }
        //基础配置
        $data['site'] = [
            //视频广告出现频率
            'config_ad_number' => Cache::get('config_ad_number', 0),
            //财务配置
            'config_jewel_alias' => Cache::get('config_jewel_alias'),
            'config_recharge_jewel_rate' => Cache::get('config_recharge_jewel_rate'),
            //启动页配置
            'config_start_ad_link' => Cache::get('config_start_ad_link'),
            'config_start_ad_type' => Cache::get('config_start_ad_type'),
            'config_start_ad_img' => Storage::url(Cache::get('config_start_ad_img')),
            'config_start_ad_video' => Storage::url(Cache::get('config_start_ad_video')),
            'config_start_ad_times' => Cache::get('config_start_ad_times'),
            'config_start_ad_status' => Cache::get('config_start_ad_status'),
            //分享域名
            'config_share_domain' => Cache::get('config_share_domain'),
            //视频页顶部菜单
            'config_video_top_menu_0' => Cache::get('config_video_top_menu_0'),
            'config_video_top_menu_0_action' => Cache::get('config_video_top_menu_0_action'),
            'config_video_top_menu_0_status' => Cache::get('config_video_top_menu_0_status'),

            'config_video_top_menu_1' => Cache::get('config_video_top_menu_1'),
            'config_video_top_menu_1_action' => Cache::get('config_video_top_menu_1_action'),
            'config_video_top_menu_1_status' => Cache::get('config_video_top_menu_1_status'),

            'config_video_top_menu_2' => Cache::get('config_video_top_menu_2'),
            'config_video_top_menu_2_action' => Cache::get('config_video_top_menu_2_action'),
            'config_video_top_menu_2_status' => Cache::get('config_video_top_menu_2_status'),
            //用户协议
            'config_agreement_show' => Cache::get('config_agreement_show'),
            'config_agreement_version' => Cache::get('config_agreement_version'),
            'config_agreement_alert' => Cache::get('config_agreement_alert'),
            'config_agreement_register' => Cache::get('config_agreement_register'),
            'config_agreement_privacy' => Cache::get('config_agreement_privacy'),
            //站点图标
            'config_site_logo_ico' => Storage::url(Cache::get('config_site_logo_ico')),
            //默认用户头像
            'config_default_avatar' => Storage::url(Cache::get('config_default_avatar')),
            //用户注册方式
            'config_user_type' => Cache::get('config_user_type'),
            //注册协议、隐私策略
            'config_register_agreement_url' => Cache::get('config_register_agreement_url'),
            'config_privacy_policy_url' => Cache::get('config_privacy_policy_url')
        ];
        //商城分类
        $data['categories'] = GoodsCategory::orderBy('sort', 'desc')->get();
        $data['categories'] = new GoodsCategoryCollection($data['categories']);
        //直播分类
        if(Schema::hasTable('lives')){
            $data['live'] = DB::table('live_categories')->select('id','title')->orderBy('sort','desc')->get();
        }else{
            $data['live'] = [];
        }
        $data['timestamp'] = time();
        return response()->json($data);
    }
}
