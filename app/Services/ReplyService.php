<?php

namespace App\Services;

use App\Models\Boards\Board;
use Cache;
use Gate;
use App\Models\Boards\Reply;
use Illuminate\Support\Collection;

use App\Exceptions\QpickHttpException;

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
        $boardCollect = $postCollect->board;

        // 댓글 사용 여부
        if (!auth()->user()->can('checkUsableReply', $boardCollect)) {
            throw new QpickHttpException(403, 'reply.disable.board_option');
        }

        // 게시글 숨김 여부
        if (auth()->user()->can('isHidden', $postCollect)) {
            throw new QpickHttpException(403, 'reply.disable.post_hidden');
        }

        return true;
    }
}
