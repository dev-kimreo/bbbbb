<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Closure;
use Cache;

use App\Models\Board;
use App\Models\BoardOption;

use App\Http\Requests\Admins\Boards\CreateBoardsRequest;
use App\Http\Requests\Admins\Boards\ModifyBoardsRequest;

use App\Libraries\CollectionLibrary;


class BoardController extends Controller
{

    /**
     * @OA\Post(
     *      path="/admin/board",
     *      summary="게시판 생성",
     *      description="게시판 생성",
     *      operationId="adminBoardCreate",
     *      tags={"-게시판"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name","type"},
     *              @OA\Property(property="name", type="string", ref="#/components/schemas/Board/properties/name" ),
     *              @OA\Property(property="type", type="string", ref="#/components/schemas/Board/properties/type" ),
     *              @OA\Property(property="hidden", type="string", ref="#/components/schemas/Board/properties/hidden" ),
     *              @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully Created",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="successfully Modified" ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
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
     *                              @OA\Property(property="100002", ref="#/components/schemas/RequestResponse/properties/100002"),
     *                              @OA\Property(property="100022", ref="#/components/schemas/RequestResponse/properties/100022"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="100073", ref="#/components/schemas/RequestResponse/properties/100073"),
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
     * 게시판 생성
     */
    public function create(CreateBoardsRequest $request) {
        /**
         * 옵션
         */
        $optArrs = $request->options;

        $optDefaultArrs = $this->funcGetOptionList(['sel' => ['type', 'default']])->toArray();

        if ( is_array($optArrs) && count($optArrs) ) {


            foreach ($optArrs as $type => $val) {

                // 옵션 데이터
                $tags = separateTag('board.options.info');

                $data = Cache::tags($tags)->remember($type, config('cache.custom.expire.common'), function() use ($type){
                    $opt = BoardOption::where('type', $type);
                    $opt = $opt->first();

                    if (!$opt) {
                        return false;
                    }

                    $opt = $opt->toArray();

                    return $opt;
                });

                // 옵션 데이터 존재하지 않을 경우
                if ( !$data ) {
                    Cache::tags($tags)->forget($type);
                    return response()->json(getResponseError(100022, 'options.type'), 422);
                }

                // 옵션 값 체크
                $valCheck = true;
                switch($type){
                    case 'thema':
                        $optArrs[$type] = isset($val) ? $val : $data['default'];
                        break;
                    default:
                        $valArrs = array_column($data['options'], 'value');
                        if ( !in_array( $val, $valArrs ) ) {
                            $valCheck = false;
                        }
                        break;
                }

                if ( !$valCheck ) {
                    return response()->json(getResponseError(100022, 'options.' . $type . '.value'), 422);
                }

                unset($data);
            }

            foreach ($optDefaultArrs as $k => $arr) {
                if ( !isset($optArrs[$arr['type']]) ) {
                    $optArrs[$arr['type']] = $arr['default'];
                }
            }

        } else {
            $defaultOpt = [];
            foreach ($optDefaultArrs as $k => $arr) {
                $defaultOpt[$arr['type']] = $arr['default'];
            }
        }

        $board = new Board;
        $board->name = $request->name;
        $board->type = $request->type;

        if ( $request->hidden ) {
            $board->hidden = $request->hidden;
        }

        $board->options = is_array($optArrs) && count($optArrs) ? $optArrs : $defaultOpt;

        $board->save();

        // 게시판 목록 데이터 cache flush
        Cache::tags(['board.list'])->flush();

        return response()->json([
            'message' => __('common.created')
        ], 200);
    }


    /**
     * @OA\Patch(
     *      path="/admin/board/{id}",
     *      summary="게시판 수정",
     *      description="게시판 수정",
     *      operationId="adminBoardModify",
     *      tags={"-게시판"},
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
     *          response=200,
     *          description="successfully Modified",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="successfully Modified" ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100003", ref="#/components/schemas/RequestResponse/properties/100003"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="100081", ref="#/components/schemas/RequestResponse/properties/100081"),
     *                              @OA\Property(property="100083", ref="#/components/schemas/RequestResponse/properties/100083"),
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
     * 게시판 정보 수정
     */
    public function modify(ModifyBoardsRequest $request) {

        $board = Board::where('id', $request->id);
        $boardData = $board->first();
        if ( !$boardData ) {
            return response()->json(getResponseError(100022, '{id}'), 422);
        }

        // 변경 할 사항
        $uptArrs = [];

        if ( isset($request->name) ) {
             $uptArrs ['name'] = $request->name;
        }

        if ( isset($request->options) && is_array($request->options) ) {
            /**
             * 옵션
             */
            $optArrs = $request->options;

            foreach ($optArrs as $type => $val) {

                // 옵션 데이터
                $tags = separateTag('board.options.info');

                $data = Cache::tags($tags)->remember($type, config('cache.custom.expire.common'), function() use ($type){
                    $opt = BoardOption::where('type', $type);
//                      $opt->whereJsonContains('options', ['value' => $val]);
                    $opt = $opt->first();

                    if (!$opt) {
                        return false;
                    }

                    $opt = $opt->toArray();

                    return $opt;
                });

                // 옵션 데이터 존재하지 않을 경우
                if ( !$data ) {
                    Cache::tags($tags)->forget($type);
                    return response()->json(getResponseError(100022, 'options.type'), 422);
                }

                // 옵션 값 체크
                $valCheck = true;
                switch($type){
                    case 'thema':
                        break;
                    default:
                        $valArrs = array_column($data['options'], 'value');
                        if ( !in_array( $val, $valArrs ) ) {
                            $valCheck = false;
                        }
                        break;
                }

                if ( !$valCheck ) {
                    return response()->json(getResponseError(100022, 'options.' . $type . '.value'), 422);
                }

                $uptArrs['options->' . $type] = $val;
                unset($data);
            }
        }

        // 변경사항이 있을 경우
        if ( count($uptArrs) ) {
            $board->update($uptArrs);

            // 게시판 목록 데이터 cache flush
            Cache::tags(['board.list'])->flush();

            // 변경된 게시판 cache forget
            Cache::tags(['board.' . $request->id])->flush();
        }

        return response()->json([
            'message' => __('common.modified')
        ], 200);
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
     *          description="successfully Modified",
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
    public function getList(Request $request) {

        // 게시판 목록 데이터
        $tags = separateTag('board.list');

        $data = Cache::tags($tags)->remember('common', config('cache.custom.expire.common'), function(){
            $brd = Board::select(['id', 'name', 'type', 'options'])->get();
            $brd = $brd->toArray();

            return $brd;
        });

        return response()->json(CollectionLibrary::toCamelCase(collect($data)), 200);
    }

    /**
     *
     */




    /**
     * @OA\Get(
     *      path="/admin/board/options",
     *      summary="게시판 옵션 목록",
     *      description="게시판 옵션 목록",
     *      operationId="adminBoardOptionList",
     *      tags={"-게시판"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully Modified",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object", ref="#/components/schemas/BoardOption"),
     *          )
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    /**
     * 게시판 옵션 정보
     */
    public function getOptionList(Request $request) {
        $data = $this->funcGetOptionList()->toArray();

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
     *          description="successfully Modified",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object", ref="#/components/schemas/BoardOption"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100005", ref="#/components/schemas/RequestResponse/properties/100005"),
     *                          ),
     *                      }
     *                  ),
     *              )
     *          )
     *      ),
     *  )
     */
    /**
     * 게시판 상세 정보
     * @param Request $request
     * @return mixed
     */
    public function getBoardInfo(Request $request) {
        $board = $this->funcGetBoard($request->id);

        if ( !$board ) {
            return response()->json(getResponseError(100005), 422);
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($board)));
    }




    public function reInitBoardOption(Request $request) {
        $this->funcReInitBoardOption();
    }



    /**
     * 게시판 옵션 검색 메소드
     * @param array $set
     * @return mixed
     */
    static public function funcGetOptionList($set = []){
        $tags = separateTag('board.options.list');

        $data = Cache::tags($tags)->remember(md5(json_encode($set)), config('cache.custom.expire.common'), function() use ($set) {
            $opt = new BoardOption;

            if ( isset($set['sel']) ) {
                $opt = $opt->select($set['sel']);
            }

            $opt = $opt->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();

            return $opt;
        });

        return $data;
    }

    /**
     * @param $boardId
     * @return mixed
     */
    static public function funcGetBoard($boardId) {
        $tags = separateTag('board.' . $boardId);

        $data = Cache::tags($tags)->remember('info', config('cache.custom.expire.common'), function() use ($boardId) {
            $opt = Board::find($boardId);

            if (!$opt) {
                return false;
            }

            return $opt;
        });

        if ( !$data ) {
            Cache::tags($tags)->forget('info');
            return false;
        }

        return $data;
    }




    static public function funcReInitBoardOption() {

        $board = Board::all();
        $boardOpt = BoardOption::select(['type', 'default'])->get();
        $boardOpt = $boardOpt->toArray();
        $optArrs = [];
        foreach ( $boardOpt as $k => $arr ) {
            $optArrs[$arr['type']] = $arr['default'];
        }
        $typeArrs = array_keys($optArrs);

        $board = $board->toArray();

        foreach ( $board as $k => &$arr ) {
            $keys = array_keys($arr['options']);

            $diffArrs = array_diff($typeArrs, $keys);
            if ( count($diffArrs) ) {
                foreach ($diffArrs as $dv) {
                    $arr['options'][$dv] = $optArrs[$dv];
                }
            }

            Board::where('id', $arr['id'])->update(['options' => $arr['options']]);
        }




    }


}
