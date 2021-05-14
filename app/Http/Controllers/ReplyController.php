<?php


namespace App\Http\Controllers;

use App\Libraries\CollectionLibrary;
use Illuminate\Http\Request;
use Auth;
use Cache;


use App\Http\Controllers\BoardController as BoardController;
use App\Http\Controllers\PostController as PostController;

use App\Models\Reply;
use App\Models\Post;

use App\Http\Requests\Replies\CreateRepliesRequest;
use App\Http\Requests\Replies\ModifyRepliesRequest;
use App\Http\Requests\Replies\DeleteRepliesRequest;
use App\Http\Requests\Replies\GetListRepliesRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\PaginationLibrary;

use App\Services\BoardService;
use App\Services\PostService;
use App\Services\ReplyService;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class ReplyController extends Controller
{
    public $attachType = 'reply';

    public function __construct(Reply $reply, BoardService $boardService, PostService $postService, ReplyService $replyService)
    {
        $this->reply = $reply;
        $this->boardService = $boardService;
        $this->postService = $postService;
        $this->replyService = $replyService;
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
     *          response=200,
     *          description="생성되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="생성되었습니다." ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="실패",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100005", ref="#/components/schemas/RequestResponse/properties/100005"),
     *                              @OA\Property(property="100022", ref="#/components/schemas/RequestResponse/properties/100022"),
     *                              @OA\Property(property="100041", ref="#/components/schemas/RequestResponse/properties/100041"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="200005", ref="#/components/schemas/RequestResponse/properties/200005"),
     *                              @OA\Property(property="250001", ref="#/components/schemas/RequestResponse/properties/250001"),
     *                          ),
     *                      }
     *                  ),
     *              )
     *          )
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    public function create(CreateRepliesRequest $request)
    {
        // 댓글 사용 여부 체크
        $this->replyService->checkUse($request->boardId, $request->postId);

        // 댓글 작성
        $this->reply->post_id = $request->postId;
        $this->reply->user_id = auth()->user()->id;
        $this->reply->content = $request->content;
        $this->reply->save();

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.' . $request->postId . '.reply'])->flush();

        return response()->json([
            'message' => __('common.created')
        ], 200);
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
     *          response=200,
     *          description="수정되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="수정되었습니다." ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="실패",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100005", ref="#/components/schemas/RequestResponse/properties/100005"),
     *                              @OA\Property(property="100041", ref="#/components/schemas/RequestResponse/properties/100041"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="101001", ref="#/components/schemas/RequestResponse/properties/101001"),
     *                              @OA\Property(property="200005", ref="#/components/schemas/RequestResponse/properties/200005"),
     *                              @OA\Property(property="210003", ref="#/components/schemas/RequestResponse/properties/210003"),
     *                              @OA\Property(property="250001", ref="#/components/schemas/RequestResponse/properties/250001"),
     *                          ),
     *                      }
     *                  ),
     *              )
     *          )
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    /**
     * @param ModifyRepliesRequest $request
     * @return mixed
     */
    public function modify(ModifyRepliesRequest $request)
    {
        // 댓글 사용 여부
        $this->replyService->checkUse($request->boardId, $request->postId);

        $replyCollect = $this->reply::find($request->id);

        // 댓글 수정 권한 체크
        if (!auth()->user()->can('update', $replyCollect)) {
            throw new QpickHttpException(403, 'reply.disable.writer_only');
        }

        $replyCollect->content = $request->content;
        $replyCollect->update();

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.' . $request->postId . '.reply'])->flush();

        return response()->json([
            'message' => __('common.modified')
        ], 200);
    }


    /**
     * @OA\Delete(
     *      path="/v1/board/{boardId}/post/{postId}/reply/{id}",
     *      summary="댓글 삭제",
     *      description="댓글 삭제",
     *      operationId="replyDelete",
     *      tags={"게시판 댓글"},
     *      @OA\Response(
     *          response=200,
     *          description="삭제되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="삭제되었습니다." ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="실패",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100005", ref="#/components/schemas/RequestResponse/properties/100005"),
     *                              @OA\Property(property="100041", ref="#/components/schemas/RequestResponse/properties/100041"),
     *                              @OA\Property(property="101001", ref="#/components/schemas/RequestResponse/properties/101001"),
     *                              @OA\Property(property="200005", ref="#/components/schemas/RequestResponse/properties/200005"),
     *                              @OA\Property(property="210003", ref="#/components/schemas/RequestResponse/properties/210003"),
     *                              @OA\Property(property="250001", ref="#/components/schemas/RequestResponse/properties/250001"),
     *                          ),
     *                      }
     *                  ),
     *              )
     *          )
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    /**
     * @param DeleteRepliesRequest $request
     * @return mixed
     */
    public function delete(DeleteRepliesRequest $request)
    {
        $replyCollect = $this->reply::find($request->id);

        // 삭제 권한 체크
        if (!auth()->user()->can('delete', $replyCollect)) {
            throw new QpickHttpException(403, 'reply.disable.writer_only');
        }

        // 댓글 소프트 삭제
        $replyCollect->delete();

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.' . $request->postId . '.reply'])->flush();

        return response()->json([
            'message' => __('common.deleted')
        ], 200);
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
     *          description="success",
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
     *          response=422,
     *          description="failed get lists",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100005", ref="#/components/schemas/RequestResponse/properties/100005"),
     *                              @OA\Property(property="100022", ref="#/components/schemas/RequestResponse/properties/100022"),
     *                              @OA\Property(property="100041", ref="#/components/schemas/RequestResponse/properties/100041"),
     *                              @OA\Property(property="100051", ref="#/components/schemas/RequestResponse/properties/100051"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="101001", ref="#/components/schemas/RequestResponse/properties/101001"),
     *                              @OA\Property(property="200005", ref="#/components/schemas/RequestResponse/properties/200005"),
     *                              @OA\Property(property="250001", ref="#/components/schemas/RequestResponse/properties/250001"),
     *                          ),
     *                      }
     *                  ),
     *              )
     *          )
     *      ),
     *  )
     */
    /**
     * @param GetListRepliesRequest $request
     * @return mixed
     */
    public function getList(GetListRepliesRequest $request)
    {
        // 댓글 사용 여부
        $this->replyService->checkUse($request->boardId, $request->postId);

        // 댓글 목록
        $set = [
            'boardId' => $request->boardId,
            'postId' => $request->postId,
            'page' => $request->page,
            'view' => $request->perPage,
            'select' => ['id', 'user_id', 'content', 'hidden', 'created_at', 'updated_at']
        ];

        // where 절 eloquent
        $whereModel = $this->reply::where(['post_id' => $set['postId']]);


        // pagination
        $pagination = PaginationLibrary::set($set['page'], $whereModel->count(), $set['view']);

        if ($set['page'] <= $pagination['totalPage']) {
            // 데이터 cache
            $hash = substr(md5(json_encode($set)), 0, 5);
            $tags = separateTag('board.' . $set['boardId'] . '.post.' . $set['postId'] . '.reply');
            $data = Cache::tags($tags)->remember($hash, config('cache.custom.expire.common'), function () use ($set, $pagination, $whereModel) {
                $reply = $whereModel
                    ->with('user:id,name')
                    ->select($set['select']);

                $reply = $reply
                    ->skip($pagination['skip'])
                    ->take($pagination['perPage'])
                    ->orderBy('id', 'asc');

                $reply = $reply->get();

                // 데이터 가공
                $reply->pluck('user')->each->setAppends([]);
                foreach ($reply as $index) {
                    // 유저 이름
                    $index->userName = $index->user->toArray()['name'];
                    unset($index->user);
                }

                return $reply;
            });
        }

        $data = isset($data) ? $data->toArray() : array();

        $result = ['header' => $pagination];
        $result['list'] = $data;

        return response()->json(CollectionLibrary::toCamelCase(collect($result)), 200);
    }
}