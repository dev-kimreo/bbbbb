<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\Posts\DestroyRequest;
use App\Http\Requests\Posts\GetListRequest;
use App\Http\Requests\Posts\IndexRequest;
use App\Http\Requests\Posts\StoreRequest;
use App\Http\Requests\Posts\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Models\Attach\AttachFile;
use App\Models\Board;
use App\Models\Post;
use App\Models\Reply;
use App\Models\Users\User;
use App\Services\AttachService;
use Auth;
use DB;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class PostController extends Controller
{
    private Post $post;
    private Board $board;
    private AttachService $attachService;

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
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *      @OA\Property(property="sort", type="integer", example=999, description="게시글 전시순서" ),
     *      @OA\Property(property="hidden", type="integer", example=1, description="게시글 전시여부 (0:전시, 1:미전시)" ),
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
     *              @OA\Property(ref="#/components/schemas/Post"),
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
     * @return JsonResponse
     * @throws QpickHttpException
     */

    public function store(StoreRequest $request, $boardId): JsonResponse
    {
        // 게시판 정보
        $this->board = $this->board->findOrFail($boardId);

        // check write post Policy
        if (!Auth::user()->can('create', [$this->post, $this->board])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 게시글 작성
        $this->post->board_id = $boardId;
        $this->post->user_id = Auth::id();
        $this->post->title = $request->input('title');
        $this->post->content = $request->input('content');
        $this->post->sort = $request->input('sort', 999);
        $this->post->hidden = $request->input('hidden', 1);
        $this->post->save();

        // response
        return response()->json(collect($this->getOne($this->post->id)), 201);
    }


    /**
     * @OA\Schema (
     *      schema="postModify",
     *      @OA\Property(property="boardId", type="integer", example=3, description="변경할 게시판 고유 번호" ),
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *      @OA\Property(property="sort", type="integer", example=999, description="게시글 전시순서" ),
     *      @OA\Property(property="hidden", type="integer", example=1, description="게시글 전시여부 (0:전시, 1:미전시)" ),
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
     *              @OA\Property(ref="#/components/schemas/Post"),
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
     * @param $id
     * @return JsonResponse
     * @throws QpickHttpException
     */

    public function update(UpdateRequest $request, $boardId, $id): JsonResponse
    {
        // find
        $this->post = $this->post->where('board_id', $boardId)->findOrFail($id);

        // check update post Policy
        if (!Auth::user()->can('update', $this->post)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // query
        $this->post->board_id = $request->input('board_id', $this->post->board_id);
        $this->post->title = $request->input('title', $this->post->title);
        $this->post->content = $request->input('content', $this->post->content);
        $this->post->sort = $request->input('sort', $this->post->sort);
        $this->post->hidden = $request->input('hidden', $this->post->hidden);
        $this->post->save();

        // response
        return response()->json(collect($this->getOne($this->post->id)), 201);
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
     * @param DestroyRequest $request
     * @param $boardId
     * @param $id
     * @return Response
     * @throws QpickHttpException
     */

    public function destroy(DestroyRequest $request, $boardId, $id): Response
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
     *                          @OA\Property(property="url", example="https://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="게시글 섬네일 이미지 url" ),
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
     * @param IndexRequest $request
     * @param $boardId
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request, $boardId): Collection
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

        // Backoffice login
        if (!Auth::isLoggedForBackoffice()) {
            $postModel->where('hidden', 0);
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id', 'sort']);
            $sortCollect->each(function ($item) use ($postModel) {
                $postModel->orderBy($item['key'], $item['value']);
            });
        }

        // pagination
        $pagination = PaginationLibrary::set($request->page, $postModel->count(), $request->per_page);

        if ($request->page <= $pagination['totalPage']) {
            $postModel->with('user')->withCount('replies');
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

        return collect($res);
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
     *              @OA\Property(ref="#/components/schemas/Post"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     *
     * @param int $board_id
     * @param int $post_id
     * @return Collection
     * @throws QpickHttpException
     */

    public function show(int $board_id, int $post_id): Collection
    {
        $post = $this->getOne($post_id);

        // 리소스 접근 권한 체크
        if (!Gate::allows('view', [$post, $post->board])) {
            throw new QpickHttpException(403, 'common.forbidden');
        }

        return collect($post);
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
     *              @OA\Property(property="startCreatedDate", type="date(Y-m-d)", example="2021-01-01", description="등록일자 검색 시작일"),
     *              @OA\Property(property="endCreatedDate", type="date(Y-m-d)", example="2021-01-01", description="등록일자 검색 시작일"),
     *              @OA\Property(property="hidden[]", type="boolean", example=1, description="숨김여부<br/>1: 숨김, 0: 노출"),
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
     * @param GetListRequest $request
     * @return Collection
     * @throws QpickHttpException
     */
    public function getList(GetListRequest $request): Collection
    {
        //
        $res = [];

        // Query Build
        $postModel = DB::table('posts')->select('posts.*')->whereNull('posts.deleted_at');

        // 회원정보
        $postModel->join('users', 'users.id', '=', 'posts.user_id');
        $postModel->leftJoin('user_privacy_active', 'user_privacy_active.user_id', '=', 'posts.user_id');

        // 게시판 정보
        $postModel->join('boards', 'boards.id', '=', 'posts.board_id');

        /**
         * Where
         */
        if ($s = $request->input('board_id')) {
            $postModel->where('posts.board_id', $s);
        }

        if (is_array($s = $request->input('hidden')) && !in_array(null, $s) ) {
            $postModel->whereIn('posts.hidden', $s);
        }

        if ($s = $request->input('email')) {
            $postModel->where('user_privacy_active.email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->input('name')) {
            $postModel->where('user_privacy_active.name', $s);
        }

        if ($s = $request->input('post_id')) {
            $postModel->where('posts.id', $s);
        }

        if ($s = $request->input('title')) {
            $postModel->where('posts.title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->input('start_created_date')) {
            $s = Carbon::parse($s);
            $postModel->where('posts.created_at', '>=', $s);
        }

        if ($s = $request->input('end_created_date')) {
            $s = Carbon::parse($s)->setTime(23, 59, 59);
            $postModel->where('posts.created_at', '<=', $s);
        }

        // 통합 검색
        if ($s = $request->input('multi_search')) {
            $postModel->where(function ($q) use ($s) {
                $q->orWhere('user_privacy_active.name', $s);

                if (is_numeric($s)) {
                    $q->orWhere('posts.id', $s);
                }
            });
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id', 'sort']);
            $sortCollect->each(function ($item) use ($postModel) {
                $postModel->orderBy($item['key'], $item['value']);
            });
        }


        // 게시글
        // pagination
        $pagination = PaginationLibrary::set($request->input('page'), $postModel->count(), $request->input('per_page'));

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

        return collect($res);
    }

    protected function getOne(int $post_id)
    {
        // set relations
        $with = ['user', 'attachFiles', 'thumbnail.attachFiles', 'board'];

        if (Auth::hasAccessRightsToBackoffice()) {
            $with[] = 'backofficeLogs';
        }

        // query
        $data = Post::with($with)->findOrFail($post_id);

        // 데이터 가공
        $thumbnail = $data->thumbnail->attachFiles ?? null;
        unset($data->thumbnail);
        $data->thumbnail = $thumbnail;

        // return
        return $data;
    }
}
