<?php

namespace Qihucms\App\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SmsRequest;
use App\Services\Sms\Messages\RegisterCaptchaMessage;
use App\Services\Sms\Messages\ResetPasswordCaptchaMessage;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SmsController extends Controller
{
    protected $sms;

    public function __construct(SmsService $smsService)
    {
        $this->sms = $smsService;
    }

    public function register(SmsRequest $request)
    {
        $mobile = $request->input('username');
        $code = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        // 缓存用户验证码，有效期3分钟
        Cache::put('registerCaptcha' . $mobile, $code, Carbon::now()->addMinutes(3));
        $message = new RegisterCaptchaMessage($code);
        $result = $this->sms->send($mobile, $message);
        if ($result == 'success') {
            return response()->json([
                'data' => true,
                'message' => '发送成功'
            ]);
        } else {
            return response()->json([
                'data' => false,
                'message' => '发送失败',
                'errors' => ['tips' =>'验证码发送失败']
            ], 422);
        }
    }

    public function findPassword(SmsRequest $request)
    {
        $mobile = $request->input('mobile');
        $code = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        // 缓存用户验证码，有效期3分钟
        Cache::put('findPasswordCaptcha' . $mobile, $code, Carbon::now()->addMinutes(3));
        $message = new ResetPasswordCaptchaMessage($code);
        $result = $this->sms->send($mobile, $message);
        if ($result == 'success') {
            return response()->json([
                'data' => true,
                'message' => '发送成功'
            ]);
        } else {
            return response()->json([
                'data' => $result,
                'message' => '发送失败',
                'errors' => ['tips' =>'验证码发送失败']
            ], 422);
        }
    }
}
