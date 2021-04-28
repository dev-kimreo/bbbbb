<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;


use App\Http\Controllers\BoardController as BoardController;
use App\Http\Controllers\PostController as PostController;

use App\Models\Reply;

use App\Http\Requests\Replies\CreateRepliesRequest;
use App\Http\Requests\Replies\ModifyRepliesRequest;
use App\Http\Requests\Replies\DeleteRepliesRequest;
use App\Http\Requests\Replies\GetListRepliesRequest;

use App\Libraries\PageCls;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class ReplyController extends Controller
{
    public $attachType = 'reply';

    /**
     *  @OA\Schema (
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

    public function create(CreateRepliesRequest $request) {

        // 데이터 체크
        $checkRes = $this->funcCheckUseReply($request->boardId, $request->postId);
        if ( $checkRes !== true ) {
            return response()->json($checkRes, 422);
        }

        // 댓글 작성
        $reply = new Reply;
        $reply->post_id = $request->postId;
        $reply->user_id = auth()->user()->id;
        $reply->content = $request->content;
        $reply->save();

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.' . $request->postId . '.reply'])->flush();

        return response()->json([
            'message' => __('common.created')
        ], 200);
    }


    /**
     *  @OA\Schema (
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
    public function modify(ModifyRepliesRequest $request) {

        // 데이터 체크
        $checkRes = $this->funcCheckUseReply($request->boardId, $request->postId);
        if ( $checkRes !== true ) {
            return response()->json($checkRes, 422);
        }

        $reply = Reply::find($request->id)->where('user_id', auth()->user()->id)->first();
        if ( is_null($reply) ) {
            return response()->json(getResponseError(101001), 422);
        }

        $reply->content = $request->content;
        $reply->update();

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
    public function delete(DeleteRepliesRequest $request) {

        $reply = Reply::where(['id' => $request->id, 'user_id' => auth()->user()->id])->first();
        if ( is_null($reply) ) {
            return response()->json(getResponseError(101001), 422);
        }

        // 댓글 소프트 삭제
        $reply->delete();

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
     *                      @OA\Property(property="regDate", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *                      @OA\Property(property="uptDate", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
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
    public function getList(GetListRepliesRequest $request) {
        // 데이터 체크
        $checkRes = $this->funcCheckUseReply($request->boardId, $request->postId);
        if ( $checkRes !== true ) {
            return response()->json($checkRes, 422);
        }

        // 댓글 목록
        $set = [
            'boardId' => $request->boardId,
            'postId' => $request->postId,
            'page' => $request->page,
            'view' => $request->perPage,
            'select' => ['id', 'userId', 'content', 'hidden', 'regDate', 'uptDate']
        ];

        // where 절 eloquent
        $whereModel = Reply::where(['post_id' => $set['postId']]);


        // pagination
        $pagination = PageCls::set($set['page'], $whereModel->count(), $set['view']);
        if ( !$pagination ) {
            return response()->json(getResponseError(101001), 422);
        }

        if ( $set['page'] <= $pagination['totalPage'] ) {
            // 데이터 cache
            $hash = substr(md5(json_encode($set)), 0, 5);
            $tags = separateTag('board.' . $set['boardId'] . '.post.' . $set['postId'] . '.reply');
            $data = Cache::tags($tags)->remember($hash, config('cache.custom.expire.common'), function() use ($set, $pagination, $whereModel) {
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

        return response()->json($result, 200);
    }


    public function funcCheckUseReply($boardId, $postId) {

        // 필수 파라미터 확인
        if ( !isset($boardId) ) {
            return getResponseError(100001, 'boardId');
        }

        if ( !isset($postId) ) {
            return getResponseError(100001, 'postId');
        }

        // 게시글 번호 확인
        if ( ! intval($boardId) ) {
            return getResponseError(100041, 'boardId');
        }

        if ( ! intval($postId) ) {
            return getResponseError(100041, 'postId');
        }

        // 게시글 댓글 작성 가능여부 체크
        $post = new PostController;
        $post = $post->funcGetInfo($postId, $boardId);
        if ( isset($post['errors']) ) {
            return $post;
        }
        $postInfo = $post->toArray();

        $board = new BoardController;
        $board = $board->funcGetBoard($boardId);
        if ( !$board ) {
            return getResponseError(100005, 'boardId');
        }

        $boardInfo = $board->toArray();
        if ( !$boardInfo['options']['reply'] ) {
            return getResponseError(250001);
        }

        // 게시글 숨김 여부
        if ( $postInfo['hidden'] ) {
            return getResponseError(200005);
        }

        return true;
    }


}
