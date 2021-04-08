<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Closure;
use Cache;


use App\Http\Controllers\BoardController;

use App\Models\Post;

use App\Http\Requests\Posts\GetListPostsRequest;
use App\Http\Requests\Posts\CreatePostsRequest;
use App\Http\Requests\Posts\ModifyPostsRequest;

use App\Libraries\PageCls;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class PostController extends Controller
{
    /**
     *  @OA\Schema (
     *      schema="postCreate",
     *      required={"boardNo", "userNo", "title", "content"},
     *      @OA\Property(property="boardNo", type="string", example=1, description="게시판 고유 번호" ),
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" )
     *  )
     */


    /**
     * @OA\Post(
     *      path="/v1/post",
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
     *                              @OA\Property(property="100022", ref="#/components/schemas/RequestResponse/properties/100022"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
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
     * 게시글 작성
     * @param CreatePostsRequest $request
     * @return mixed
     */
    public function create(CreatePostsRequest $request) {

        // 게시판 정보
        $board = BoardController::funcGetBoard($request->boardNo);
        if ( !$board ) {
            return response()->json(getResponseError(100022, 'boardNo'), 422);
        }
        $board = $board->toArray();

        /**
         * 게시글 작성 데이터
         */
        $etc = [];

        foreach ( $board['options'] as $type => $val ) {
            switch ($type) {
                case 'thumbnail':
                    break;

                case 'attachFile':
                    break;

                case 'boardStatus':     // 게시글 상태 사용
                    if ( isset($val) && $val ) {
                        $etc['status'] = 'wait';
                    }
                    break;
            }
        }

        $post = New Post;
        $post->board_no = $request->boardNo;
        $post->user_no = auth()->user()->id;
        $post->title = $request->title;
        $post->content = $request->content;

        if ( count($etc) ) {
            $post->etc = $etc;
        }

        $post->save();

        return response()->json([
            'message' => __('common.created'),
            'data' => [
                'no' => $post->id
            ]
        ], 200);
    }


    public function modify(ModifyPostsRequest $request) {
        echo $request->id;
        print_r($request->all());
    }


    /**
     * @OA\Schema (
     *      schema="postList",
     *      @OA\Property(property="page", type="integer", example=2, description="게시글 페이지" ),
     *      @OA\Property(property="view", type="integer", example=10, description="한 페이지당 보여질 갯수" )
     * )
     *
     * @OA\Get(
     *      path="/v1/board/{boardNo}/post",
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
     *          description="successfully Created",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
     *                      @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
     *                      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *                      @OA\Property(property="boardNo", type="integer", example=1, description="게시판 고유번호" ),
     *                      @OA\Property(property="userNo", type="integer", example=1, description="게시판 고유번호" ),
     *                  )
     *              ),
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
     *                      type="object"
     *                  )
     *              )
     *          )
     *      )
     *  )
     */
    /**
     * @param GetListPostsRequest $request
     * @return mixed
     */
    public function getList(GetListPostsRequest $request) {
        // init
        $boardInfoFlag = isset($request->boardInfo) ? $request->boardInfo : 0;

        // 게시판 정보
        $board = BoardController::funcGetBoard($request->boardNo);
        if ( !$board ) {
            return response()->json(getResponseError(10001, 'boardNo'), 422);
        }
        $board = $board->toArray();

        // 게시글 목록
        $set = [
            'boardNo' => $request->boardNo,
            'boardInfo' => $boardInfoFlag,
            'page' => $request->page,
            'view' => $request->view,
            'select' => ['id', 'title', 'comment', 'boardNo', 'userNo', 'regDate', 'uptDate']
        ];

        // where 절 eloquent
        $whereModel = Post::where(['board_no' => $set['boardNo']]);

        // 섬네일 기능 사용시 **check**
        if ( isset($board['options']['thumbnail']) && $board['options']['thumbnail'] ) {

        }

        // 글 상태 사용시
        if ( isset($board['options']['boardStatus']) && $board['options']['boardStatus'] ) {
            $set['select'][] = 'etc';
        }

        // 시크릿 기능 사용시
        if ( isset($board['options']['secret']) && $board['options']['secret'] ) {
            if ( !auth()->user() ) {
                return response()->json(getResponseError(10500), 422);
            }

            $whereModel = $whereModel->where(['user_no' => auth()->user()->id]);
        }

        // 댓글 사용시 **check**
        if ( $board['options']['reply'] ) {

        }

        // 파일 첨부 **check**
        if ( isset($board['options']['attachFile']) && $board['options']['attachFile'] ) {

        }

        // pagination
        $pagination = PageCls::set($set['page'], $whereModel->count(), $set['view']);
        if ( !$pagination ) {
            return response()->json(getResponseError(10020), 422);
        }

        if ( $set['page'] <= $pagination['totalPage'] ) {
            // cache
            $hash = substr(md5(json_encode($set)), 0, 5);
            $tags = separateTag('post.list.' . $set['boardNo']);
            $data = Cache::tags($tags)->remember($hash, config('cache.custom.expire.common'), function() use ($set, $pagination, $whereModel) {
                $post = $whereModel
                        ->with('user:id,name')
                        ->select($set['select'])
                        ->skip($pagination['skip'])
                        ->take($pagination['perPage'])
                        ->get();

                $post->pluck('user')->each->setAppends([]);

                return $post;
            });
        }

        $data = isset($data) ? $data->toArray() : array();

        $result = ['header' => $pagination];

        // 게시판 정보 필요시
        if ( $boardInfoFlag ) {
            $result['board'] = $board;
        }

        $result['list'] = $data;



        return response()->json($result, 200);
    }


}
