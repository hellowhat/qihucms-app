<?php

namespace Qihucms\App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Goods;
use App\Http\Resources\Goods\GoodsCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class GoodController extends Controller
{
    public function goods(Request $request)
    {
        // 合并条件
        $defaultWhere = [
            ['is_shelves', '=', '是'],
            ['stock', '>', 0],
            ['status', '=', 1]
        ];

        $sql = Goods::where($defaultWhere);
        $orderBy = $request->get('order','id');
        $category = $request->get('category',0);
        $limit = $request->get('limit',10);
        if($category > 0){
            $sql = $sql->where('goods_category_id',$category);
        }

        $data = $sql->orderBy($orderBy,'desc')->paginate($limit);
        return new GoodsCollection($data);
    }

    public function detail($id)
    {
        $good = Goods::find($id);
        if($good){
            $good = $good->toArray();
            $good['thumbnail'] = Storage::url($good['thumbnail']);
            if($good['media_list'] and count($good['media_list']) > 0){
                for($i = 0;$i < count($good['media_list']);$i++){
                    $good['media_list'][$i] = Storage::url($good['media_list'][$i]);
                }
            }else{
                $good['media_list'] = [];
            }
            $good['content'] = preg_replace("/<a[^>]*>/i", "", $good['content']);
            $good['content'] = preg_replace("/<\/a>/i", "", $good['content']);
            $good['content'] = preg_replace("/<p[^>]*>/i", "", $good['content']);
            $good['content'] = preg_replace("/<\/p>/i", "", $good['content']);
            $good['content'] = preg_replace("/style=.+?['|\"]/i",'',$good['content']);
            $good['content'] = preg_replace("/class=.+?['|\"]/i",'',$good['content']);
            $good['content'] = preg_replace("/width=.+?['|\"]/i",'',$good['content']);
            $good['content'] = preg_replace("/height=.+?['|\"]/i",'',$good['content']);
            $good['content'] = str_replace('<img ','<img class="content_img" ',$good['content']);
            return response()->json($good);
        }else{
            return response()->json(['message' => '找不到商品']);
        }
    }

    public function userGoods($id)
    {
        $mall = Goods::where('user_id', $id)->where('status', 1)->where('is_shelves', '是')->orderBy('created_at', 'desc')->get();
        return new GoodsCollection($mall);
    }
}
