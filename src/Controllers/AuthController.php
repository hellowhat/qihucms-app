<?php

namespace Qihucms\App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ResetPasswordRequest;

class AuthController extends Controller
{

    /**
     * 判断是否绑定，绑定直接登录，未绑定返回状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function socialite(Request $request)
    {
        $data = $request->all();

        $user = User::where('open_id->' . $data['provider'] . '->' . $data['platform'], $data['openid'])->first();
        if(!$user && !empty($data['unionid'])){
            $user = User::where('open_id->' . $data['provider'] . '->unionid', $data['unionid'])->first();
        }

        if($user){
            return response()->json([
                'status' => true,
                'token_data' => $user->createToken($user->id)
            ]);
        }else{
            return response()->json([
                'status' => false
            ]);
        }
    }

    /**
     * 绑定
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function binding(Request $request)
    {
        $data = $request->all();
        $user_id = $request->user()->id;

        $user = User::find($user_id);

        if($user->open_id){
            $user->open_id[$data['provider']][$data['platform']] = $data['openid'];
            $user->open_id[$data['provider']]['unionid'] = $data['unionid'];
        }else{
            $user->open_id = [
                $data['provider'] => [
                    $data['platform'] => $data['openid'],
                    'unionid' => $data['unionid']
                ]
            ];
        }

        $user->save();
        return response()->json([
            'status' => true
        ]);
    }

    /**
     * 用户注册
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $data = [
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
            'avatar' => Cache::get('config_default_avatar', null),
            'api_token' => Str::random(60),
        ];

        if (Cache::get('config_user_type')) {
            $data['mobile'] = $request->input('username');
        }

        $user = User::create($data);
        $token = $user->createToken($user->id);
        return response()->json($token);
    }

    /**
     * 社会化登录自动注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\GuzzleException
     */
    public function auto_register(Request $request)
    {
        $data = $request->all();

        $userBase = [
            'username' => $data['provider'],
            'password' => Hash::make(Str::random(8)),
        ];

        $client = new Client(['verify' => false]);
        $avatar = $client->request('get',$data['avatar'])->getBody()->getContents();
        $filename = 'user/avatar/'.Str::random(20).'.jpg';
        Storage::put($filename, $avatar);

        $userBase = array_merge($userBase, [
            'open_id' => [
                $data['provider'] => [
                    $data['platform'] => $data['openid'],
                    'unionid' => $data['unionid']
                ]
            ],
            'nickname' => $data['nickname'],
            'avatar' => $filename,
            'api_token' => Str::random(60)
        ]);

        $user = User::create($userBase);
        $user->username = $data['provider'].$user->id;
        $user->save();

        $token = $user->createToken($user->id);
        return response()->json($token);
    }

    // 重置密码
    public function reset(ResetPasswordRequest $request)
    {
        $mobile = $request->input('mobile');
        $password = $request->input('password', '123456');

        $res = User::where('mobile', $mobile)->update(['password' => Hash::make($password)]);

        return response()->json($res);
    }
}