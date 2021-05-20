<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;

use App\Models\Board;
use App\Models\BoardOption;

use App\Http\Requests\Boards\CreateRequest;
use App\Http\Requests\Boards\UpdateRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\CollectionLibrary;

use App\Services\BoardService;


class BoardController extends Controller
{
    private $boardService;


    public function __construct(Board $board, BoardOption $boardOption, BoardService $boardService)
    {
        $this->board = $board;
        $this->boardOption = $boardOption;
        $this->boardService = $boardService;
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
     *              @OA\Property(property="hidden", type="string", ref="#/components/schemas/Board/properties/hidden" ),
     *              @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="successfully Modified" ),
     *          )
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
     * 게시판 생성
     */
    public function create(CreateRequest $request)
    {
        // check Policy
        if (!auth()->user()->can('create', Board::class)) {
            throw new QpickHttpException(403, 'board.disable.not_permitted');
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
                case 'thema':
                case 'attachLimit':
                    break;
                default:
                    if(!collect($data)->where('value', $val)->count())
                    {
                        throw new QpickHttpException(422, 'board.option.disable.wrong_value', $requestKey);
                    }
                    break;
            }
        }

        // 쿼리
        $this->board->name = $request->name;
        $this->board->options = $opts;
        $this->board->hidden = $request->hidden ?? 0;
        $this->board->save();

        $this->board->refresh();

        // 게시판 목록 데이터 cache flush
        Cache::tags(['board.list'])->flush();

        return response()->json(CollectionLibrary::toCamelCase(collect($this->board)), 201);
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
     *              @OA\Property(property="hidden", type="string", ref="#/components/schemas/Board/properties/hidden" ),
     *              @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="successfully Modified" ),
     *          )
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
     * 게시판 정보 수정
     */
    public function modify(UpdateRequest $request)
    {
        // check Policy
        if (!auth()->user()->can('update', $this->board)) {
            throw new QpickHttpException(403, 'board.disable.not_permitted');
        }

        $this->board = $this->board->find($request->id);
        if (!$this->board) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        // 변경 할 사항
        $this->board->name = $request->name ?? $this->board->name;

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
                        if(!collect($data)->where('value', $val)->count())
                        {
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

            // 게시판 목록 데이터 cache flush
            Cache::tags(['board.list'])->flush();

            // 변경된 게시판 cache forget
            Cache::tags(['board.' . $request->id])->flush();
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($this->board)), 201);
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
     *              type="array",
     *              @OA\Items(type="object", ref="#/components/schemas/BoardOptionJson"),
     *          )
     *      )
     *  )
     */
    /**
     * 게시판 목록 정보
     */
    public function getList(Request $request)
    {
        // 게시판 목록 데이터
        $tags = separateTag('board.list');
        $ttl = config('cache.custom.expire.common');

        $data = Cache::tags($tags)->remember('common', $ttl, function () {
            return $this->board::select(['id', 'name', 'options'])->get();
        });

        return response()->json(CollectionLibrary::toCamelCase(collect($data)), 200);
    }

    /**
     *
     */


    /**
     * @OA\Get(
     *      path="/v1/board/option",
     *      summary="게시판 옵션 목록",
     *      description="게시판 옵션 목록",
     *      operationId="adminBoardOptionList",
     *      tags={"게시판"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object", ref="#/components/schemas/BoardOption"),
     *          )
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     */
    /**
     * 게시판 옵션 정보
     */
    public function getOptionList(Request $request)
    {
        $data = $this->boardService->getOptionList();

        return response()->json(CollectionLibrary::toCamelCase(collect($data)));
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
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object", ref="#/components/schemas/BoardOption"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     */
    /**
     * 게시판 상세 정보
     * @param Request $request
     * @return mixed
     */
    public function getBoardInfo(Request $request)
    {
        $board = $this->boardService->getInfo($request->id);
        return response()->json(CollectionLibrary::toCamelCase(collect($board)));
    }

    public function reInitBoardOption(Request $request)
    {
        $this->funcReInitBoardOption();
    }

    static public function funcReInitBoardOption()
    {

        $board = Board::all();
        $boardOpt = BoardOption::select(['type', 'default'])->get();
        $boardOpt = $boardOpt->toArray();
        $optArrs = [];
        foreach ($boardOpt as $k => $arr) {
            $optArrs[$arr['type']] = $arr['default'];
        }
        $typeArrs = array_keys($optArrs);

        $board = $board->toArray();

        foreach ($board as $k => &$arr) {
            $keys = array_keys($arr['options']);

            $diffArrs = array_diff($typeArrs, $keys);
            if (count($diffArrs)) {
                foreach ($diffArrs as $dv) {
                    $arr['options'][$dv] = $optArrs[$dv];
                }
            }

            Board::where('id', $arr['id'])->update(['options' => $arr['options']]);
        }


    }


}
