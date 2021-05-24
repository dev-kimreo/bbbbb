<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Models\Board;

use App\Http\Requests\Boards\StoreRequest;
use App\Http\Requests\Boards\UpdateRequest;
use App\Http\Requests\Boards\DestroyRequest;

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


    public function __construct(Board $board, BoardService $boardService)
    {
        $this->board = $board;
        $this->boardService = $boardService;
    }

    /**
     * @OA\Get(
     *      path="/v1/board",
     *      summary="게시판 목록",
     *      description="게시판 목록",
     *      operationId="adminBoardList",
     *      tags={"게시판"},
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
    public function index()
    {
        // response init
        $res = [];
        $res['header'] = [];
        $res['list'] = [];

        // 게시판 목록
        $board = $this->board::with('user:id,name')->orderBy('sort', 'asc')->orderBy('id', 'asc');

        // Bacckoffice login
        if (Auth::user()->isLoginToManagerService()) {
            $board->withCount('posts');
        } else {
            $board->where('enable', 1);
        }

        $res['list'] = $board->get();

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
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
                case 'thema':
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->board = $this->board->with('user')->findOrFail($id);

        return CollectionLibrary::toCamelCase(collect($this->board));
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
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
                    case 'thema':
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
     * @param  int  $id
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

}
