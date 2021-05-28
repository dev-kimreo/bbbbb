<?php


namespace App\Http\Controllers;

use App\Libraries\StringLibrary;
use App\Models\AttachFile;
use App\Models\Reply;
use App\Models\User;
use App\Models\Post;
use App\Models\Board;

use Illuminate\Http\Request;
use Auth;
use Cache;
use Carbon\Carbon;
use Str;
use DB;
use Gate;


use App\Http\Requests\Posts\StoreRequest;
use App\Http\Requests\Posts\UpdateRequest;
use App\Http\Requests\Posts\DestroyRequest;
use App\Http\Requests\Posts\IndexRequest;
use App\Http\Requests\Posts\GetListRequest;

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
        if (!Auth::user()->can('update', $this->post)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $this->post->title = $request->title ?? $this->post->title;
        $this->post->content = $request->content ?? $this->post->content;
        $this->post->sort = $request->sort ?? $this->post->sort;

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
        if (!Auth::user()->can('delete', $this->post)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 첨부파일 삭제
        $this->attachService->delete($this->post->attachFiles->modelKeys());

        // 소프트 삭제 진행
        $this->post->delete();

        // 댓글 삭제
        $this->post->replies()->delete();


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
     *                      @OA\Property(property="repliesCount", type="integer", example=20, description="게시글의 댓글 수" ),
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

        // 리소스 접근 권한 체크
        if (!Gate::allows('viewAny', [$this->post, $this->board->find($boardId)])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 게시글 목록
        $postModel = $this->post->where('board_id', $boardId);

        // Bacckoffice login
        if (Auth::check() && Auth::user()->isLoginToManagerService()) {

        } else {
            $postModel->where('hidden', 0);
        }

        // Sort By
        if ($request->get('sortBy')) {
            $sortCollect = CollectionLibrary::getBySort($request->get('sortBy'), ['id', 'sort']);
            $sortCollect->each(function ($item) use ($postModel) {
                $postModel->orderBy($item['key'], $item['value']);
            });
        }

        // pagination
        $pagination = PaginationLibrary::set($request->page, $postModel->count(), $request->perPage);

        if ($request->page <= $pagination['totalPage']) {
            $postModel->with('user:id,name')->withCount('replies');
            $postModel->with('thumbnail.attachFiles');

            $postModel
                ->groupBy('posts.id')
                ->skip($pagination['skip'])
                ->take($pagination['perPage']);

            $post = $postModel->get();

            // 데이터 가공
            $post->each(function (&$v) {
                $attachFiles = $v->thumbnail->attachFiles ?? null;
                unset($v->thumbnail);
                $v->thumbnail = $attachFiles;
            });


        }

        $data = $post ?? [];

        $res['header'] = $pagination;
        $res['list'] = $data;

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
        $postModel = $this->post->where('board_id', $boardId)->with('user:id,name');

        // 첨부파일 (섬네일 제외)
        $postModel->with('attachFiles');
        $postModel->with('thumbnail.attachFiles');
        $postModel = $postModel->findOrFail($id);

        // 리소스 접근 권한 체크
        if (!Gate::allows('view', [$postModel, $postModel->board])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 데이터 가공
        $attachFiles = $postModel->thumbnail->attachFiles ?? null;
        unset($postModel->thumbnail);
        $postModel->thumbnail = $attachFiles;

        return CollectionLibrary::toCamelCase(collect($postModel));
    }


    /**
     * @OA\Get(
     *      path="/v1/post",
     *      summary="[B] 게시글 목록",
     *      description="[B] 게시판 목록",
     *      operationId="postsList",
     *      tags={"게시글"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="boardId", type="integer", example=1, description="선택한 게시판 고유번호" ),
     *              @OA\Property(property="name", type="string", example="홍길동", description="등록자 검색 필드" ),
     *              @OA\Property(property="postId", type="integer", example=7, description="게시글 번호 검색 필드" ),
     *              @OA\Property(property="title", type="string", example="제목으로 검색합니다.", description="게시글 제목 검색 필드" ),
     *              @OA\Property(property="multiSearch", type="string|integer", example="전체 검색합니다.", description="통합검색을 위한 검색어"),
     *              @OA\Property(property="sortBy", type="string", example="+sort,-id", description="정렬기준<br/>+:오름차순, -:내림차순" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(
     *                      allOf = {
     *                          @OA\Schema(ref="#/components/schemas/Post"),
     *                          @OA\Schema(
     *                              @OA\Property(property="repliesCount", type="integer", example=10, description="댓글 수" ),
     *                              @OA\Property(property="attachFilesCount", type="integer", example=2, description="첨부 파일 수" )
     *                          ),
     *                          @OA\Schema(
     *                              @OA\Property(property="board", ref="#/components/schemas/Board")
     *                          ),
     *                          @OA\Schema(
     *                              @OA\Property(property="user", ref="#/components/schemas/User")
     *                          )
     *                      }
     *                  )
     *              )
     *          )
     *      )
     *  )
     */
    public function getList(GetListRequest $request)
    {
        //
        $res = [];

        // Query Build
        $postModel = DB::table('posts')->select('posts.*');

        // 회원정보
        $postModel->join('users', 'users.id', '=', 'posts.user_id');

        // 게시판 정보
        $postModel->join('boards', 'boards.id', '=', 'posts.board_id');

        /**
         * Where
         */
        if ($s = $request->get('boardId')) {
            $postModel->where('posts.board_id', $s);
        }

        if ($s = $request->get('email')) {
            $postModel->where('users.email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->get('name')) {
            $postModel->where('users.name', $s);
        }

        if ($s = $request->get('postId')) {
            $postModel->where('posts.id', $s);
        }

        if ($s = $request->get('title')) {
            $postModel->where('posts.title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        // 통합 검색
        if ($s = $request->get('multiSearch')) {
            $postModel->where(function ($q) use ($s) {
                $q->orWhere('users.name', $s);

                if (is_numeric($s)) {
                    $q->orWhere('posts.id', $s);
                }
            });
        }

        // Sort By
        if ($s = $request->get('sortBy')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id', 'sort']);
            $sortCollect->each(function ($item) use ($postModel) {
                $postModel->orderBy($item['key'], $item['value']);
            });
        }


        // 게시글
        // pagination
        $pagination = PaginationLibrary::set($request->page, $postModel->count(), $request->perPage);

        $postModel->skip($pagination['skip'])
            ->take($pagination['perPage'])
            ->groupBy('posts.id');

        $postModel = $postModel->get();

        $postModel->each(function (&$item) {
            static $users = [];
            static $boards = [];

            $item->user = $users[$item->user_id] ?? ($users[$item->user_id] = User::find($item->user_id));
            $item->board = $boards[$item->board_id] ?? ($boards[$item->board_id] = Board::find($item->board_id));

            $item->repliesCount = Reply::where('post_id', $item->id)->count();
            $item->attachFilesCount = AttachFile::where(['attachable_type' => 'post', 'attachable_id' => $item->id])->count();
        });


        $res['header'] = $pagination;
        $res['list'] = $postModel;

        return CollectionLibrary::toCamelCase(collect($res));

    }


}
