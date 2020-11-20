<?php

namespace Qihucms\App\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\GiftRepository;
use Illuminate\Http\Request;
use Qihucms\App\Resources\Gift\AdCollection;
use Illuminate\Support\Facades\Auth;

class GiftController extends Controller
{
    protected $gift;

    public function __construct(GiftRepository $gift)
    {
        $this->gift = $gift;
    }

    public function gift_api(Request $request)
    {
        $result = $this->gift->create([
            'gift_id' => $request->get('id'),
            'user_id' => Auth::id(),
            'to_user_id' => $request->get('uid'),
            'status' => 1
        ]);
        if ($result['status']) {
            return $this->successJson($result['message'], $result['data']);
        }
        return $this->errorJson($result['message'], $result['data']);
    }

    public function gift_list()
    {
        // 礼物列表
        $gifts = $this->gift->allGift();
        return new AdCollection($gifts);
    }
}
