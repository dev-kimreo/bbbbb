<?php


namespace App\Http\Controllers;

use App\Libraries\CollectionLibrary;
use Illuminate\Http\Request;
use Auth;
use Cache;

use App\Models\Post;
use App\Models\Reply;

use App\Http\Requests\Replies\StoreRequest;
use App\Http\Requests\Replies\UpdateRequest;
use App\Http\Requests\Replies\DestroyRequest;
use App\Http\Requests\Replies\IndexRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\PaginationLibrary;


/**
 * Class PostController
 * @package App\Http\Controllers
 */
class ReplyController extends Controller
{
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
     *              @OA\Property(property="message", type="string", example="생성되었습니다." ),
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
     */

    public function store(StoreRequest $request, $boardId, $postId)
    {
        $this->post = $this->post::where('board_id', $boardId)->findOrFail($postId);

        // 댓글 사용 유무 체크
        if (!$this->post->board->options['reply']) {
            throw new QpickHttpException(403, 'reply.disable.board_option');
        }

        // 댓글 작성
        $this->reply->post_id = intval($request->postId);
        $this->reply->user_id = auth()->user()->id;
        $this->reply->content = $request->content;
        $this->reply->save();

        $this->reply->refresh();

        return response()->json(CollectionLibrary::toCamelCase(collect($this->reply)), 201);
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
     *          description="modified."
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
     */
    /**
     * @param UpdateRequest $request
     * @return mixed
     */
    public function update(UpdateRequest $request, $boardId, $postId, $id)
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

        $this->reply->content = $request->content ?? $this->reply->content;
        $this->reply->update();

        return response()->json(CollectionLibrary::toCamelCase(collect($this->reply)), 201);
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
     */
    /**
     * @param DestroyRequest $request
     * @return mixed
     */
    public function destroy(DestroyRequest $request, $boardId, $postId, $id)
    {
        $this->reply = $this->reply::findOrFail($id);

        // 삭제 권한 체크
        if (!auth()->user()->can('delete', $this->reply)) {
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
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer", example=1, description="댓글 고유번호" ),
     *                      @OA\Property(property="content", type="string", example="댓글 내용입니다.", description="댓글 내용" ),
     *                      @OA\Property(property="hidden", type="integer", example=0, default=0, description="게시글 숨김 여부<br/>0:노출<br/>1:숨김" ),
     *                      @OA\Property(property="userName", type="string", example="홍길동", description="작성자" ),
     *                      @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
     *                      @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *                      @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
     *                  )
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
     */
    /**
     * @param IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request, $boardId, $postId)
    {
        $this->post = $this->post::where('board_id', $boardId)->findOrFail($postId);

        // 댓글 사용 유무 체크
        if (!$this->post->board->options['reply']) {
            throw new QpickHttpException(403, 'reply.disable.board_option');
        }

        // where 절 eloquent
        $this->reply = $this->reply::where('post_id', $postId);

        // pagination
        $pagination = PaginationLibrary::set($request->page, $this->reply->count(), $request->perPage);


        if ($request->page <= $pagination['totalPage']) {
            $this->reply = $this->reply->with('user:id,name');
            $this->reply = $this->reply
                ->skip($pagination['skip'])
                ->take($pagination['perPage'])
                ->orderBy('id', 'asc');

            $this->reply = $this->reply->get();
        }

        $data = $this->reply ?? array();

        $result = ['header' => $pagination];
        $result['list'] = $data;

        return response()->json(CollectionLibrary::toCamelCase(collect($result)), 200);
    }
}
