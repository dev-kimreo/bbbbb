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



class BoardController extends Controller
{

    /**
     * @OA\Post(
     *      path="/admin/board",
     *      summary="게시판 생성",
     *      description="게시판 생성",
     *      operationId="adminBoardCreate",
     *      tags={"게시판"},
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
     *              @OA\Property(property="message", type="string", example="successfully Created" ),
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
     *                      @OA\Property(
     *                          property="20010",
     *                          type="object",
     *                          description="이미 존재하는 type 입니다.",
     *                          @OA\Property(
     *                              property="key",
     *                              type="string",
     *                              description="type",
     *                              example="type",
     *                          ),
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="20401",
     *                          type="object",
     *                          description="옵션 및 옵션 값을 확인해주세요.",
     *                          @OA\Property(
     *                              property="key",
     *                              type="string",
     *                              description="options.editor.value",
     *                              example="options.editor.value",
     *                          ),
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                  )
     *              )
     *          )
     *      )
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

        if ( is_object($optArrs) && count($optArrs) ) {
            foreach ($optArrs as $type => $val) {

                // 옵션 데이터
                $tags = separateTag('board.options.' . $type);

                $data = Cache::tags($tags)->remember('info', 10, function() use ($type){
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
                    return response()->json(getResponseError(20401, 'options.type'), 422);
                }

                // 옵션 값 체크
                $valCheck = true;
                switch($type){
                    default:
                        $valArrs = array_column($data['options'], 'value');
                        if ( !in_array( $val, $valArrs ) ) {
                            $valCheck = false;
                        }
                        break;
                }

                if ( !$valCheck ) {
                    return response()->json(getResponseError(20401, 'options.' . $type . '.value'), 422);
                }

                unset($data);
            }
        } else {

            $defaultOpt = [];
            $optDefaultArrs = $this->funcGetOptionList(['sel' => ['type', 'default']]);

            foreach ($optDefaultArrs as $k => $arr) {
                $defaultOpt[$arr['type']] = $arr['default'];
            }
        }

        $board = new Board;
        $board->name = $request->name;
        $board->type = $request->type;
        $board->hidden = $request->hidden ?? 0;
        $board->options = is_object($request->options) && count($request->options) ? $request->options : $defaultOpt;

        $board->save();

        return response()->json([
            'message' => __('board.created')
        ], 200);
    }


    /**
     * @OA\Patch(
     *      path="/admin/board/{type}",
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
     *                      @OA\Property(
     *                          property="20401",
     *                          type="object",
     *                          description="옵션 및 옵션 값을 확인해주세요.",
     *                          @OA\Property(
     *                              property="key",
     *                              type="string",
     *                              description="options.editor.value",
     *                              example="options.editor.value",
     *                          ),
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                  )
     *              )
     *          )
     *      )
     *  )
     */

    /**
     * 게시판 정보 수정
     */
    public function modify(ModifyBoardsRequest $request) {

        $board = Board::where('type', $request->type);
        $boardData = $board->first();
        if ( !$boardData ) {
            return response()->json(getResponseError(20401, '{type}'), 422);
        }

        // 변경 할 사항
        $uptArrs = [];

        if ( isset($request->name) ) {
             $uptArrs ['name'] = $request->name;


        if ( isset($request->hidden) ) {
            $uptArrs ['hidden'] = $request->hidden;
        }

        if ( isset($request->options) && is_array($request->options) ) {
            /**
             * 옵션
             */
            $optArrs = $request->options;

            foreach ($optArrs as $type => $val) {

                // 옵션 데이터
                $tags = separateTag('board.options.' . $type);

                $data = Cache::tags($tags)->remember('info', 10, function() use ($type){
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
                    return response()->json(getResponseError(20401, 'options.type'), 422);
                }

                // 옵션 값 체크
                $valCheck = true;
                switch($type){
                    default:
                        $valArrs = array_column($data['options'], 'value');
                        if ( !in_array( $val, $valArrs ) ) {
                            $valCheck = false;
                        }
                        break;
                }

                if ( !$valCheck ) {
                    return response()->json(getResponseError(20401, 'options.' . $type . '.value'), 422);
                }

                $uptArrs['options->' . $type] = $val;

                unset($data);
            }
        }

        // 변경사항이 있을 경우
        if ( count($uptArrs) ) {
            $board->update($uptArrs);
        }

        return response()->json([
            'message' => __('board.modified')
        ], 200);
    }



    /**
     * @OA\Get(
     *      path="/admin/board",
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
     *      ),
     *  )
     */
    /**
     * 게시판 목록 정보
     */
    public function getList(Request $request) {

        // 게시판 목록 데이터
        $tags = separateTag('board.list');

        $data = Cache::tags($tags)->remember('info', 10, function(){
            $brd = Board::select(['id', 'name', 'type', 'options'])->get();
            $brd = $brd->toArray();

            return $brd;
        });

        return response()->json($data, 200);
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
     *      tags={"게시판"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully Modified",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object", ref="#/components/schemas/BoardOption"),
     *          )
     *      ),
     *  )
     */
    /**
     * 게시판 옵션 정보
     */
    public function getOptionList(Request $request) {
        $data = $this->funcGetOptionList();

        return response()->json($data);
    }


    static public function funcGetOptionList($set = []){
        $tags = separateTag('board.options');

        $data = Cache::tags($tags)->remember('list', 10, function() use ($set) {
            $opt = new BoardOption;

            if ( isset($set['sel']) ) {
                $opt = $opt->select($set['sel']);
            }

            $opt = $opt->orderBy('sort', 'asc')->orderBy('id', 'asc')->get()->toArray();

            return $opt;
        });

        return $data;
    }



}
