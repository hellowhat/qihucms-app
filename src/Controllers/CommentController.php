<?php

namespace Qihucms\App\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentShortVideoRequest;
use App\Http\Resources\CommentShortVideo\Comment as CommentResource;
use App\Http\Resources\CommentShortVideo\CommentCollection;
use App\Http\Resources\CommentShortVideo\Reply as ReplyResource;
use App\Http\Resources\CommentShortVideo\ReplyCollection;
use App\Models\ShortVideo;
use App\Repositories\CommentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    protected $comment;

    public function __construct(CommentRepository $comment)
    {
        $this->comment = $comment;
    }

    public function index(Request $request)
    {
        if ($request->has('comment_id')) {
            // 回复
            return new ReplyCollection($this->comment->getReplyWhereCommentId($request->get('comment_id')));
        } else {
            // 评论
            return new CommentCollection($this->comment->shortVideoPaginate($request->get('content_id')));
        }
    }

    public function store(CommentShortVideoRequest $request)
    {
        if ($request->has('content_id')) {
            $short_video_id = $request->input('content_id', 0);
            $to_user_id = ShortVideo::where('id', $short_video_id)->value('user_id');
            if ($to_user_id) {
                // 评论
                $result = $this->comment->storeComment([
                    'content_type' => 'short_video',
                    'content_id' => $short_video_id,
                    'to_user_id' => $to_user_id,
                    'user_id' => $request->user()->id,
                    'content' => $request->input('content'),
                    'status' => Cache::get('config_check_comment', 0)
                ]);
                if ($result) {
                    return new CommentResource($result);
                }
            }
        } elseif ($request->has('comment_id')) {
            $comment = $this->comment->findComment($request->input('comment_id'));
            // 回复评论
            $result = $this->comment->storeReply([
                'user_id' => $request->user()->id,
                'to_user_id' => $comment->user_id,
                'comment_id' => $request->input('comment_id'),
                'reply_type' => 1,
                'reply_id' => 0,
                'content' => $request->input('content'),
                'status' => Cache::get('config_check_comment', 0)
            ]);
            if ($result) {
                return new ReplyResource($result);
            }
        } elseif ($request->has('reply_id')) {
            $reply = $this->comment->findReply($request->input('reply_id'));
            // 回复回复
            $result = $this->comment->storeReply([
                'user_id' => $request->user()->id,
                'to_user_id' => $reply->user_id,
                'comment_id' => $reply->comment_id,
                'reply_type' => 1,
                'reply_id' => $reply->id,
                'content' => $request->input('content'),
                'status' => Cache::get('config_check_comment', 0)
            ]);
            if ($result) {
                return new ReplyResource($result);
            }
        }
        return $this->errorJson('发布失败');
    }

    public function commentLike(Request $request)
    {
        $user_id = Auth::id();
        $id = $request->get('id');
        $result = $this->comment->commentLike($user_id, $id);
        return $this->successJson('操作成功', $result);
    }

    public function replyLike(Request $request)
    {
        $user_id = Auth::id();
        $id = $request->get('id');
        $result = $this->comment->replyLike($user_id, $id);
        return $this->successJson('操作成功', $result);
    }
}
