<?php

namespace App\Http\Controllers\Boards;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Replies\DestroyRequest;
use App\Http\Requests\Replies\IndexRequest;
use App\Http\Requests\Replies\StoreRequest;
use App\Http\Requests\Replies\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Boards\Post;
use App\Models\Boards\Reply;
use Auth;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class ReplyController extends Controller
{
    private Reply $reply;
    private Post $post;
    public string $exceptionEntity = "reply";

    public function __construct(Reply $reply, Post $post)
    {
        $this->reply = $reply;
        $this->post = $post;
    }

    /**
     * @OA\Schema (
     *      schema="replyCreate",
     *      required={"content"},
     *      @OA\Property(property="content", type="string", example="댓글 내용입니다.", description="댓글 내용" )
     *  )
     */

    /**
     * @OA\Post(
     *      path="/v1/board/{boardId}/post/{postId}/reply",
     *      summary="댓글 작성",
     *      description="댓글 작성",
     *      operationId="replyCreate",
     *      tags={"게시판 댓글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/replyCreate"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/Reply")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     * @param StoreRequest $request
     * @param $boardId
     * @param $postId
     * @return JsonResponse
     * @throws QpickHttpException
     */

    public function store(StoreRequest $request, $boardId, $postId): JsonResponse
    {
        $this->post = $this->post::where('board_id', $boardId)->findOrFail($postId);

        // 댓글 사용 유무 체크
        if (!$this->post->board->options['reply']) {
            throw new QpickHttpException(403, 'reply.disable.board_option');
        }

        // check write post Policy
        if (!auth()->user()->can('create', [$this->reply, $this->post, $this->post->board])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 댓글 작성
        $this->reply->post_id = intval($postId);
        $this->reply->user_id = Auth::id();
        $this->reply->content = $request->input('content');
        $this->reply->save();

        return response()->json($this->getOne($this->reply->id), 201);
    }


    /**
     * @OA\Schema (
     *      schema="replyModify",
     *      required={"content"},
     *      @OA\Property(property="content", type="string", example="댓글 내용입니다.", description="댓글 내용" )
     *  )
     */

    /**
     * @OA\Patch(
     *      path="/v1/board/{boardId}/post/{postId}/reply/{id}",
     *      summary="댓글 수정",
     *      description="댓글 수정",
     *      operationId="replyModify",
     *      tags={"게시판 댓글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/replyModify"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/Reply")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     * @param UpdateRequest $request
     * @param $boardId
     * @param $postId
     * @param $id
     * @return JsonResponse
     * @throws QpickHttpException
     */

    public function update(UpdateRequest $request, $boardId, $postId, $id): JsonResponse
    {
        $this->post = $this->post::where('board_id', $boardId)->findOrFail($postId);

        // 댓글 사용 유무 체크
        if (!$this->post->board->options['reply']) {
            throw new QpickHttpException(403, 'reply.disable.board_option');
        }

        $this->reply = $this->reply::findOrFail($id);

        // 댓글 수정 권한 체크
        if (!auth()->user()->can('update', $this->reply)) {
            throw new QpickHttpException(403, 'reply.disable.writer_only');
        }

        $this->reply->content = $request->input('content', $this->reply->content);
        $this->reply->update();

        return response()->json($this->getOne($id), 201);
    }


    /**
     * @OA\Delete(
     *      path="/v1/board/{boardId}/post/{postId}/reply/{id}",
     *      summary="댓글 삭제",
     *      description="댓글 삭제",
     *      operationId="replyDelete",
     *      tags={"게시판 댓글"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted."
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     * @param DestroyRequest $request
     * @param $boardId
     * @param $postId
     * @param $id
     * @return Response
     * @throws QpickHttpException
     */

    public function destroy(DestroyRequest $request, $boardId, $postId, $id): Response
    {
        $this->reply = $this->reply::findOrFail($id);

        // 삭제 권한 체크
        if (!Auth::user()->can('delete', $this->reply)) {
            throw new QpickHttpException(403, 'reply.disable.writer_only');
        }

        // 댓글 소프트 삭제
        $this->reply->delete();

        return response()->noContent();
    }


    /**
     * @OA\Schema (
     *      schema="replyList",
     *      @OA\Property(property="page", type="integer", example=1, default=1, description="댓글 페이지" ),
     *      @OA\Property(property="perPage", type="integer", example=15, description="한 페이지당 보여질 댓글 수" )
     * )
     *
     * @OA\Get(
     *      path="/v1/board/{boardId}/post/{postId}/reply",
     *      summary="댓글 목록",
     *      description="댓글 목록",
     *      operationId="replyGetList",
     *      tags={"게시판 댓글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/replyList"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/Reply")
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     * @param IndexRequest $request
     * @param $boardId
     * @param $postId
     * @return array
     * @throws QpickHttpException
     */

    public function index(IndexRequest $request, $boardId, $postId): array
    {
        $this->post = $this->post::where('board_id', $boardId)->findOrFail($postId);

        // 댓글 사용 유무 체크
        if (!$this->post->board->options['reply']) {
            throw new QpickHttpException(403, 'reply.disable.board_option');
        }

        // 리소스 접근 권한 체크
        if (!Gate::allows('viewAny', [$this->reply, $this->post, $this->post->board])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // where 절 eloquent
        $reply = $this->reply::where('post_id', $postId)->with('user');

        // pagination
        $pagination = PaginationLibrary::set($request->input('page'), $reply->count(), $request->input('per_page'));
        $reply->skip($pagination['skip'])->take($pagination['perPage']);

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($reply) {
                $reply->orderBy($item['key'], $item['value']);
            });
        }

        // result
        return [
            'header' => $pagination,
            'list' => $reply->get() ?? []
        ];
    }

    protected function getOne(int $id)
    {
        return Reply::with('user')->findOrFail($id);
    }
}
