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
            throw new QpickHttpException(422, 100005);
        }
        $boardId = $postCollect['board_id'];

        // 게시판 정보
        $boardCollect = $this->boardService->getInfo($boardId);
        $boardInfo = $boardCollect->toArray();


        // 데이터 cache
        $tags = separateTag('board.' . $boardId . '.post.' . $postId);
        $data = Cache::tags($tags)->remember('info', config('cache.custom.expire.common'), function () use ($postId, $boardId, $boardInfo) {
            $select = ['posts.id', 'title', 'board_id', 'content', 'hidden', 'posts.etc', 'posts.user_id', 'posts.created_at', 'posts.updated_at'];

            // 섬네일 지원 게시판일 경우
            if ($boardInfo['options']['thumbnail']) {
                $select[] = 'af.url AS thumbnail';
                $select[] = 'af.id AS thumbNo';
            }

            // 게시글 답변 지원 게시판 일 경우
            if ($boardInfo['options']['boardReply']) {
                $select[] = 'comment';
            }

            $post = $this->post->select($select)->where(['posts.id' => $postId, 'board_id' => $boardId]);

            // 섬네일 사용
            if ($boardInfo['options']['thumbnail']) {
                $post = $post->leftjoin('attach_files AS af', function ($join) {
                    $join
                        ->on('posts.id', '=', 'af.type_id')
                        ->where('type', $this->attachType)
                        ->whereJsonContains('af.etc', ['type' => 'thumbnail']);
                });
            }

            $post = $post->first();

            if (!$post) {
                return false;
            }

            $post->thumbnail = [
                'id' => $post->thumbNo,
                'url' => $post->thumbnail
            ];
            unset($post->thumbNo);

            // 기타정보 가공
            if (isset($post->etc['status'])) {
                $post->status = __('common.post.etc.status.' . $post->etc['status']);
            }

            // 게시글 추가 정보 (회원)
            $post->userName = $post->user->toArray()['name'];
            unset($post->user);

            return $post;
        });

        if (!$data) {
            Cache::tags($tags)->forget('info');
            throw new QpickHttpException(422, 100005);
        }

        return $data;
    }

}
