<?php
use Illuminate\Routing\Router;
use Encore\Admin\Layout\Content;

//app小程序接口,需要登录
Route::name('api.app.')
    ->middleware('auth:api')
    ->prefix('_app')
    ->namespace('Qihucms\App\Controllers')
    ->group(function () {
        //读取用户信息
        Route::get('user_info', 'UserController@info');
        //读取消息列表
        Route::get('notices', 'UserController@notices');
        // 会员关注接口
        Route::get('update_follow_api', 'UserController@updateFollowApi')->name('update_follow_api');
        // 视频点赞接口
        Route::get('like_short_video_api', 'VideoController@userLikeVideo')->name('like_short_video_api');
        // 获取VIP真实地址接口
        Route::get('get_video_src_api', 'VideoController@getVipVideoUrl')->name('get_video_src_api');
        // 评论点赞接口
        Route::get('like_comment_api', 'CommentController@commentLike')->name('like_comment_api');
        // 回复点赞接口
        Route::get('like_reply_api', 'CommentController@replyLike')->name('like_reply_api');
        // 发表评论接口
        Route::post('comment_publish_api', 'CommentController@store')->name('comment_publish_api');
        // 赠送礼物接口
        Route::get('gift_api', 'GiftController@gift_api')->name('gift_api');
        // 用户绑定
        Route::post('binding', 'AuthController@binding')->name('banding');
        //发送信息
        Route::post('live/message', 'LiveController@message')->name('message');
        //直播抢红包
        Route::post('live/rob', 'LiveController@rob')->name('rob');
        //直播发红包
        Route::post('live/redbag', 'LiveController@redBagPublish')->name('redbag');
    });
//app小程序接口,无需登录
Route::name('api.app.')
    ->middleware('api')
    ->prefix('_app')
    ->namespace('Qihucms\App\Controllers')
    ->group(function () {
        // 视频接口
        Route::get('short_video_api', 'VideoController@getVideoApi')->name('short_video_api');
        // 视频分享
        Route::get('video_share_api', 'VideoController@getVideoShare')->name('video_share_api');
        // 短视频广告接口
        Route::get('ad_short_video_api', 'VideoController@getAd')->name('ad_short_video_api');
        // 更新视频观看数接口
        Route::post('update_video_count_api', 'VideoController@updateLookCount')->name('update_video_count_api');
        // 读取评论
        Route::get('comment_list_api', 'CommentController@index')->name('comment_list_api');
        // 礼物列表
        Route::get('gift_list', 'GiftController@gift_list')->name('gift_list');

        //读取服务器配置
        Route::get('config_env', 'ConfigController@env')->name('config.env');

        //读取产品列表
        Route::get('good_list', 'GoodController@goods')->name('good_list');
        //产品详情
        Route::get('good/detail/{id}', 'GoodController@detail')->name('good_detail');
        // 用户商品
        Route::get('good/user/{id}', 'GoodController@userGoods')->name('good_user');

        //社会化登录用户注册
        Route::post('auto_register', 'AuthController@auto_register')->name('auto_register');
        //用户注册
        Route::post('register', 'AuthController@register')->name('register');
        //社会化登录检测
        Route::post('socialite', 'AuthController@socialite')->name('socialite');
        // 注册验证码
        Route::post('sms/register', 'SmsController@register')->name('sms.register');
        // 重置密码验证码
        Route::post('sms/reset_password', 'SmsController@findPassword')->name('sms.reset_password');
        // 修改密码
        Route::post('find_password', 'AuthController@reset')->name('reset');
        // 用户主页
        Route::get('homepage/{id}', 'UserController@homepage')->name('homepage');
        // 直播间
        Route::get('live/room/{id}', 'LiveController@room')->name('live.room');
    });
    
Route::group([
    'prefix' => config('admin.route.prefix'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->resource('plugins/qihucms/app/app_menus', '\Qihucms\App\Admin\Controllers\AppMenuController');
    $router->get('plugins/qihucms/app/config', function (Content $content) {
        return $content
            ->title('APP配置')
            ->body(new Qihucms\App\Admin\Forms\App);
    });
    $router->get('plugins/qihucms/app/agreement', function (Content $content) {
        return $content
            ->title('APP用户协议')
            ->body(new Qihucms\App\Admin\Forms\Agreement);
    });
});