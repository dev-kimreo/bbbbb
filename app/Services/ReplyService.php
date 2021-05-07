<?php

namespace App\Services;

use App\Models\Board;
use Cache;
use Gate;
use App\Models\Reply;
use Illuminate\Support\Collection;

use App\Services\BoardService;
use App\Services\PostService;

class ReplyService
{
    private $reply, $boardService, $postService;

    /**
     * PostService constructor.
     * @param Post $post
     */
    public function __construct(Reply $reply, BoardService $boardService, PostService $postService)
    {
        $this->reply = $reply;
        $this->boardService = $boardService;
        $this->postService = $postService;
    }

    public function checkUse($boardId, $postId)
    {
        $postCollect = $this->postService->getInfo($postId);
        $boardCollect = $this->boardService->getInfo($boardId);

        // 댓글 사용 여부
        if (!auth()->user()->can('checkUsableReply', $boardCollect)) {
            return getResponseError(250001);
        }

        // 게시글 숨김 여부
        if (auth()->user()->can('isHidden', $postCollect)) {
            return getResponseError(200005);
        }

        return true;
    }
}
