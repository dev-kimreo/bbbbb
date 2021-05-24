<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;
use Carbon\Carbon;
use Str;

use App\Models\Post;
use App\Models\Board;

use App\Http\Requests\Posts\StoreRequest;
use App\Http\Requests\Posts\UpdateRequest;
use App\Http\Requests\Posts\DestroyRequest;
use App\Http\Requests\Posts\IndexRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\PaginationLibrary;
use App\Libraries\CollectionLibrary;

use App\Services\AttachService;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class PostController extends Controller
{
    public function __construct(Post $post, Board $board, AttachService $attachService)
    {
        $this->post = $post;
        $this->board = $board;
        $this->attachService = $attachService;
    }

    /**
     * @OA\Schema (
     *      schema="postCreate",
     *      required={"title", "content"},
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" )
     *  )
     */


    /**
     * @OA\Post(
     *      path="/v1/board/{boardId}/post",
     *      summary="게시판 글 작성",
     *      description="게시판 글 작성",
     *      operationId="postCreate",
     *      tags={"게시판 글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/postCreate"
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

    /**
     * 게시글 작성
     * @param StoreRequest $request
     * @return mixed
     */
    public function store(StoreRequest $request)
    {
        // 게시판 정보
        $this->board = $this->board->findOrFail($request->boardId);

        // check write post Policy
        if (!auth()->user()->can('create', [$this->post, $this->board])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 게시글 작성
        $this->post->board_id = $request->boardId;
        $this->post->user_id = auth()->user()->id;
        $this->post->title = $request->title;
        $this->post->content = $request->content;

        $this->post->save();

        $this->post->refresh();

        return response()->json(CollectionLibrary::toCamelCase(collect($this->post)), 201);
    }


    /**
     * @OA\Schema (
     *      schema="postModify",
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" )
     *  )
     */

    /**
     * @OA\Patch(
     *      path="/v1/board/{boardId}/post/{id}",
     *      summary="게시판 글 수정",
     *      description="게시판 글 수정",
     *      operationId="postModify",
     *      tags={"게시판 글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/postModify"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="수정되었습니다." ),
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

    /**
     * @param UpdateRequest $request
     * @return mixed
     */
    public function update(UpdateRequest $request, $boardId, $id)
    {
        $this->post = $this->post->where('board_id', $boardId)->findOrFail($id);

        // check update post Policy
        if (!auth()->user()->can('update', $this->post)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $this->post->title = $request->title ?? $this->post->title;
        $this->post->content = $request->content ?? $this->post->content;

        // 변경사항이 있을 경우
        if ($this->post->isDirty()) {
            $this->post->save();
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($this->post)), 201);
    }


    /**
     * @OA\delete(
     *      path="/v1/board/{boardId}/post/{id}",
     *      summary="게시판 글 삭제",
     *      description="게시판 글 삭제",
     *      operationId="postDelete",
     *      tags={"게시판 글"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
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
    public function destroy(DestroyRequest $request, $boardId, $id)
    {
        $this->post = $this->post->where('board_id', $boardId)->findOrfail($id);

        // check update post Policy
        if (!auth()->user()->can('delete', $this->post)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 첨부파일 삭제
        $this->attachService->delete($this->post->attachFiles->modelKeys());

        // 소프트 삭제 진행
        $this->post->delete();

        return response()->noContent();
    }


    /**
     * @OA\Schema (
     *      schema="postList",
     *      @OA\Property(property="page", type="integer", example=1, default=1, description="게시글 페이지" ),
     *      @OA\Property(property="perPage", type="integer", example=15, description="한 페이지당 보여질 갯 수" )
     * )
     *
     * @OA\Get(
     *      path="/v1/board/{boardId}/post",
     *      summary="게시판 글 목록",
     *      description="게시판 글 목록",
     *      operationId="postGetList",
     *      tags={"게시판 글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/postList"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
     *                      @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
     *                      @OA\Property(property="thumbnail", type="object",
     *                          @OA\Property(property="url", example="http://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="게시글 섬네일 이미지 url" ),
     *                      ),
     *                      @OA\Property(property="replyCount", type="integer", example=20, description="게시글의 댓글 수" ),
     *                      @OA\Property(property="user", type="object", description="작성자" ),
     *                      @OA\Property(property="boardId", type="integer", example=1, description="게시판 고유번호" ),
     *                      @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
     *                      @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *                      @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
     *                  )
     *              ),
     *          )
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
    public function index(IndexRequest $request, $boardId)
    {
        $res = [];
        $res['header'] = [];
        $res['list'] = [];

        // Bacckoffice login
//        if (Auth::check() && Auth::user()->isLoginToManagerService()) {
//
//        } else {
            // 게시글 목록
            $this->post = $this->post->where('board_id', $boardId);

            // pagination
            $pagination = PaginationLibrary::set($request->page, $this->post->count(), $request->perPage);

            if ($request->page <= $pagination['totalPage']) {
                $this->post = $this->post->with('user:id,name')->withCount('reply');
                $this->post = $this->post->with('thumbnail.attachFiles');

                $this->post = $this->post
                    ->groupBy('posts.id')
                    ->skip($pagination['skip'])
                    ->take($pagination['perPage'])
                    ->orderBy('sort', 'asc')
                    ->orderBy('id', 'desc');

                $post = $this->post->get();

                // 데이터 가공
                $post->each(function(&$v) {
                    $attachFiles = $v->thumbnail->attachFiles ?? null;
                    unset($v->thumbnail);
                    $v->thumbnail = $attachFiles;
                });


            }

            $data = $post ?? [];

            $res['header'] = $pagination;
            $res['list'] = $data;
//        }

        return CollectionLibrary::toCamelCase(collect($res));
    }



    /**
     * @OA\Get(
     *      path="/v1/board/{boardId}/post/{id}",
     *      summary="게시판 글 상세",
     *      description="게시판 글 상세",
     *      operationId="postGetInfo",
     *      tags={"게시판 글"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
     *              @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
     *              @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *              @OA\Property(property="hidden", type="integer", example=0, default=0, description="게시글 숨김 여부<br/>0:노출<br/>1:숨김" ),
     *              @OA\Property(property="thumbnail", type="object", description="게시글 섬네일 이미지 정보",
     *                  @OA\Property(property="id", type="integer", example=4, description="이미지 고유 번호" ),
     *                  @OA\Property(property="url", type="string", example="http://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="이미지 url" ),
     *              ),
     *              @OA\Property(property="userName", type="string", example="홍길동", description="작성자" ),
     *              @OA\Property(property="boardId", type="integer", example=1, description="게시판 고유번호" ),
     *              @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
     *              @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *              @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     */

    /**
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request, $boardId, $id)
    {
        // 게시글 정보
        $this->post = $this->post->where('board_id', $boardId)->with('user:id,name');

        // 첨부파일 (섬네일 제외)
        $this->post = $this->post->with('attachFiles');
        $this->post = $this->post->with('thumbnail.attachFiles');
        $this->post = $this->post->findOrFail($id);

        // 데이터 가공
        $attachFiles = $this->post->thumbnail->attachFiles ?? null;
        unset($this->post->thumbnail);
        $this->post->thumbnail = $attachFiles;

        return CollectionLibrary::toCamelCase(collect($this->post));
    }


}
