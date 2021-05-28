<?php

namespace App\Http\Controllers;

use App\Libraries\StringLibrary;
use App\Models\Post;
use Illuminate\Http\Request;

use Auth;
use DB;
use Str;
use Gate;

use App\Models\Board;

use App\Http\Requests\Boards\StoreRequest;
use App\Http\Requests\Boards\UpdateRequest;
use App\Http\Requests\Boards\DestroyRequest;

use App\Http\Requests\Boards\GetPostsCountRequest;
use App\Http\Requests\Boards\UpdateBoardSortRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\CollectionLibrary;

use App\Services\BoardService;


class BoardController extends Controller
{

    /**
     * @OA\Schema(
     *     schema="boardInfo",
     *     allOf={
     *          @OA\Schema(ref="#/components/schemas/Board"),
     *          @OA\Schema(ref="#/components/schemas/BoardOptionJson")
     *     }
     * )
     */


    public function __construct(Board $board, Post $post, BoardService $boardService)
    {
        $this->board = $board;
        $this->post = $post;
        $this->boardService = $boardService;
    }

    /**
     * @OA\Get(
     *      path="/v1/board",
     *      summary="게시판 목록",
     *      description="게시판 목록",
     *      operationId="adminBoardList",
     *      tags={"게시판"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, description="페이지" ),
     *              @OA\Property(property="perPage", type="integer", example=15, description="한 페이지에 보여질 수" ),
     *              @OA\Property(property="sortBy", type="string", example="+sort,-id", description="정렬기준<br/>+:오름차순, -:내림차순" )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(ref="#/components/schemas/boardInfo")
     *              )
     *          )
     *      )
     *  )
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 리소스 접근 권한 체크
        if (!Gate::allows('viewAny', [$this->board])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // response init
        $res = [];
        $res['header'] = [];
        $res['list'] = [];

        // 게시판 목록
        $boardModel = $this->board::with('user:id,name');


        // Sort By
        if ($request->get('sortBy')) {
            $sortCollect = CollectionLibrary::getBySort($request->get('sortBy'), ['id', 'sort']);
            $sortCollect->each(function ($item) use ($boardModel) {
                $boardModel->orderBy($item['key'], $item['value']);
            });
        }

        // Bacckoffice login
        if (Auth::check() && Auth::user()->isLoginToManagerService()) {
            $boardModel->withCount('posts');
        } else {
            $boardModel->where('enable', 1);
        }

        $res['list'] = $boardModel->get();

        return CollectionLibrary::toCamelCase(collect($res));

    }


    /**
     * @OA\Post(
     *      path="/v1/board",
     *      summary="게시판 생성",
     *      description="게시판 생성",
     *      operationId="adminBoardCreate",
     *      tags={"게시판"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", ref="#/components/schemas/Board/properties/name" ),
     *              @OA\Property(property="enable", type="string", ref="#/components/schemas/Board/properties/enable" ),
     *              @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/boardInfo")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        // 리소스 접근 권한 체크
        if (!Auth::user()->can('create', $this->board)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 게시판 옵션 기본값 가져오기
        $opts = [];
        $this->boardService->getOptionList(['sel' => ['type', 'default']])->each(
            function ($v) use (&$opts) {
                $opts[$v->type] = $v->default;
            }
        );

        // 요청 파라미터로 입력받은 옵션 처리
        foreach ($request->options ?? [] as $type => $val) {
            if (!$val) {
                continue;
            }

            // 옵션 데이터에 선택할 수 없는 값이 들어간 경우의 오류처리
            $requestKey = 'options[' . $type . ']';
            $data = $this->boardService->getOptiontByType($type, $requestKey)->options;

            // 옵션 값 체크
            switch ($type) {
                case 'theme':
                case 'attachLimit':
                    break;
                default:
                    if (!collect($data)->where('value', $val)->count()) {
                        throw new QpickHttpException(422, 'board.option.disable.wrong_value', $requestKey);
                    }
                    break;
            }

            $opts[$type] = $val;
        }

        // 쿼리
        $this->board->user_id = Auth::user()->id;
        $this->board->name = $request->name;
        $this->board->options = $opts;

        if (isset($request->enable)) {
            $this->board->enable = $request->enable;
        }

        $this->board->save();

        $this->board->sort = $this->board->id;
        $this->board->save();

        $this->board->refresh();

        return response()->json(CollectionLibrary::toCamelCase(collect($this->board)), 201);
    }


    /**
     * @OA\Get(
     *      path="/v1/board/{id}",
     *      summary="게시판 상세 정보",
     *      description="게시판 상세 정보",
     *      operationId="adminBoardInfo",
     *      tags={"게시판"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/boardInfo")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     */
    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $boardModel = $this->board->with('user')->findOrFail($id);

        // 리소스 접근 권한 체크
        if (!Gate::allows('view', [$boardModel])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        return CollectionLibrary::toCamelCase(collect($boardModel));
    }

    /**
     * @OA\Patch(
     *      path="/v1/board/{id}",
     *      summary="게시판 수정",
     *      description="게시판 수정",
     *      operationId="adminBoardModify",
     *      tags={"게시판"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", ref="#/components/schemas/Board/properties/name" ),
     *              @OA\Property(property="enable", type="string", ref="#/components/schemas/Board/properties/enable" ),
     *              @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/boardInfo")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     */
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $this->board = $this->board->findOrfail($id);

        // 변경 할 사항
        $this->board->name = $request->name ?? $this->board->name;
        $this->board->enable = $request->enable ?? $this->board->enable;

        if (isset($request->options) && is_array($request->options)) {
            /**
             * 옵션
             */
            $optArrs = $request->options;

            foreach ($optArrs as $type => $val) {

                // 옵션 데이터
                $requestKey = 'options[' . $type . ']';
                $data = $this->boardService->getOptiontByType($type, $requestKey)->options;

                // 옵션 값 체크
                switch ($type) {
                    case 'theme':
                    case 'attachLimit':
                        break;
                    default:
                        if (!collect($data)->where('value', $val)->count()) {
                            throw new QpickHttpException(422, 'board.option.disable.wrong_value', $requestKey);
                        }
                        break;
                }

                $uptArrs['options'][$type] = $val;
                unset($data);
            }

            $this->board->options = array_merge($this->board->options, $uptArrs['options']);
        }

        // 변경사항이 있을 경우
        if ($this->board->isDirty()) {
            $this->board->save();
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($this->board)), 201);
    }


    /**
     * @OA\delete(
     *      path="/v1/board/{id}",
     *      summary="게시판 삭제",
     *      description="게시판 삭제",
     *      operationId="boardDelete",
     *      tags={"게시판"},
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
     *          "admin_auth":{}
     *      }}
     *  )
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyRequest $request, $id)
    {
        $this->board = $this->board::withCount('posts')
            ->findOrFail($id);

        if ($this->board->posts_count > 0) {
            throw new QpickHttpException(422, 'board.delete.disable.exists_post');
        }

        $this->board->delete();

        return response()->noContent();
    }


    /**
     * @OA\Get(
     *      path="/v1/board/posts-count",
     *      summary="[B] 게시판 목록(게시글 수 포함)",
     *      description="게시판 목록",
     *      operationId="boardPostsCountList",
     *      tags={"게시판"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
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
     *              @OA\Property(property="header", type="object" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1, description="게시판 고유번호<br/>전체 탭은 해당 값이 NULL"),
     *                      @OA\Property(property="name", type="string", example="공지사항", description="게시판 명"),
     *                      @OA\Property(property="postsCount", type="integer", example=41, description="게시글 수")
     *                  )
     *              )
     *          )
     *      )
     *  )
     */
    public function getPostsCount(GetPostsCountRequest $request)
    {
        // Sort By
        if ($request->get('sortBy')) {
            $sortCollect = CollectionLibrary::getBySort($request->get('sortBy'), ['id', 'sort']);
            $sortCollect->each(function ($item) {
                $this->board = $this->board->orderBy($item['key'], $item['value']);
            });
        }

        // res
        $collect = $this->board->select('id', 'name', 'sort')->get()->keyBy('id');
        data_fill($collect, '*.posts_count', 0);

        // 게시판의 글 수
        $postModel = DB::table('posts')->selectRaw('posts.board_id, count(posts.id) as posts_count')->groupBy('board_id');
        $postModel->join('users', 'posts.user_id', '=', 'users.id');

        // Where
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

        $postModel = $postModel->get();

        // 데이터 가공
        $postModel->each(function ($v) use (&$collect) {
            $collect->get($v->board_id)->posts_count = $v->posts_count ?? 0;
        });

        // 전체 글 수
        $collect->prepend(collect(['id' => null, 'name' => '전체', 'posts_count' => $collect->sum('posts_count')]));

        $res = [];
        $res['header'] = [];
        $res['list'] = $collect;

        return CollectionLibrary::toCamelCase(collect($res));
    }



    /**
     * @OA\Patch(
     *      path="/v1/board/{id}/sort",
     *      summary="[B] 게시판 전시 순서 변경",
     *      description="게시판 전시 순서 변경",
     *      operationId="updateBoardSort",
     *      tags={"게시판"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"target", "direction"},
     *              @OA\Property(property="target", type="integer", example=6, description="타겟이 될 게시판의 고유 번호" ),
     *              @OA\Property(property="direction", type="string", example="top", description="타켓 게시판보다 위, 아래 어느쪽에 위치할지 <br/>top:위, bottom:아래" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully",
     *          @OA\JsonContent()
     *      )
     *  )
     */
    public function updateBoardSort(UpdateBoardSortRequest $request, $id)
    {
        // 타겟 게시판 고유 번호
        $target = $request->get('target');
        // 타겟 게시판의 어느 방향 위치 할지
        $d = $request->get('direction');

        $selectBoardModel = $this->board->findOrFail($id);
        $targetBoardModel = $this->board->findOrFail($target);

        $selectSort = $selectBoardModel->sort;
        $targetSort = $targetBoardModel->sort;


        if ($selectBoardModel->sort < $targetBoardModel->sort) {
            $targetSort -= $d == 'bottom' ? 0 : 1;
            $changeSort = -1;

        } else {
            $targetSort += $d == 'bottom' ? 1 : 0;
            $changeSort = +1;
        }

        // 변경될 대상과 타겟의 대상이 다를 경우에만 처리
        if ($selectSort != $targetSort) {
            $sortArea = [$targetSort, $selectSort + (-1 * $changeSort)];
            sort($sortArea);

            $updateModel = $this->board->whereBetween('sort', $sortArea);

            if ($changeSort > 0) {
                $updateModel->increment('sort', abs($changeSort));
            } else {
                $updateModel->decrement('sort', abs($changeSort));
            }

            $selectBoardModel->sort = $targetSort;
            $selectBoardModel->save();
        }

        return response()->noContent();
    }

}
