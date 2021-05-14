<?php

namespace App\Services;

use Cache;
use App\Models\Post;
use Illuminate\Support\Collection;

use App\Services\BoardService;

class PostService
{
    private $post, $boardService;

    /**
     * PostService constructor.
     * @param Post $post
     */
    public function __construct(Post $post, BoardService $boardService)
    {
        $this->post = $post;
        $this->boardService = $boardService;
    }

    /**
     * @param $postId
     * @return Post
     * @throws \Exception
     */
    public function getInfo($postId): Post
    {
        $postCollect = $this->post->select('board_id')->where('id', $postId)->first();
        if (!$postCollect) {
            throw new QpickHttpException(422, 'common.not_found');
        }
        $alias = $postCollect->getMorphClass();
        $boardId = $postCollect['board_id'];

        // 게시판 정보
        $boardCollect = $this->boardService->getInfo($boardId);
        $boardInfo = $boardCollect->toArray();

        // 데이터 cache
        $tags = separateTag('board.' . $boardId . '.post.' . $postId);
        $data = Cache::tags($tags)->remember('info', config('cache.custom.expire.common'), function () use ($postId, $boardId, $boardInfo, $alias) {
            $select = ['posts.id', 'title', 'board_id', 'content', 'hidden', 'posts.user_id', 'posts.created_at', 'posts.updated_at'];

            $post = $this->post->select($select)->where(['posts.id' => $postId, 'board_id' => $boardId]);

            // 첨부 파일
            $post = $post
                ->with(['attachFiles' => function ($query) {
                    $query->select('id', 'url', 'attachable_id', 'attachable_type', 'etc');
                }]);

            $post = $post->first();

            if (!$post) {
                return false;
            }

            // 데이터 가공
            $post->attachFiles->each(function ($v, $k) use ($post, $boardInfo) {
                $etc = $v->etc;
                unset($v->attachable_id, $v->attachable_type, $v->etc);

                // 첨부 파일 중 섬네일 구분
                if (isset($etc['type']) && $etc['type'] == 'thumbnail') {
                    if ($boardInfo['options']['thumbnail']) {
                        $post->thumbnail = $v;
                        $post->attachFiles->forget($k);
                    }
                }
            });

            // 다차원을 일차원으로 단순화
            $attachFileFlatten = $post->attachFiles->flatten();
            unset($post->attachFiles);
            $post->attachFiles = $attachFileFlatten;

            return $post;
        });

        if (!$data) {
            Cache::tags($tags)->forget('info');
            throw new QpickHttpException(422, 'common.not_found');
        }

        return $data;
    }

}
