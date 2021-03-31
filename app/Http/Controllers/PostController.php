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
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * 게시판 생성
     */
    public function create(CreatePostsRequest $request) {
        $post = New Post;
        $post->board_no = $request->boardNo;
        $post->user_no = auth()->user()->id;
        $post->title = $request->title;
        $post->content = $request->content;

        $post->save();

        return response()->json([
            'message' => __('common.created')
        ], 200);
    }


    public function modify(ModifyPostsRequest $request) {
        echo $request->id;
        print_r($request->all());
    }


    /**
     * @OA\Schema (
     *      schema="postList",
     *      required={"boardNo"},
     *      @OA\Property(property="boardNo", type="string", example=1, description="게시판 고유 번호" ),
     *      @OA\Property(property="page", type="integer", example=2, description="게시글 페이지" ),
     *      @OA\Property(property="view", type="integer", example=10, description="한 페이지당 보여질 갯수" )
     * )
     *
     * @OA\Get(
     *      path="/v1/post/{boardNo}",
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
     *              @OA\Property(property="header", type="object",
     *                  @OA\Property(property="totalCnt", type="integer", example=56, description="총 글 수" ),
     *                  @OA\Property(property="totalPage", type="integer", example=6, description="총 페이지 수" ),
     *              ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
     *                      @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
     *                      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
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
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
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
            'page' => $request->page ? intval($request->page) : 1,
            'view' => $request->view ? intval($request->view) : 15,
            'select' => ['id', 'title', 'comment', 'boardNo', 'userNo', 'regDate', 'uptDate']
        ];

        // where 절 eloquent
        $whereModel = Post::where(['board_no' => $set['boardNo']]);

        // 섬네일 기능 사용시
        if ( $board['options']['thumbnail'] ) {
            
        }

        // 글 상태 사용시
        if ( $board['options']['boardStatus'] ) {
            $set['select'][] = 'etc';
        }

        // 시크릿 기능 사용시
        if ( $board['options']['secret'] ) {
            if ( !auth()->user() ) {
                return response()->json(getResponseError(10500), 422);
            }

            $whereModel = $whereModel->where(['user_no' => auth()->user()->id]);
        }

        // total 갯수
        $header = [];
        $header['totalCnt'] = $whereModel->count();
        $header['totalPage'] = ceil($header['totalCnt'] / $set['view']);

        if ( $set['page'] <= $header['totalPage'] ) {
            // cache
            $hash = substr(md5(json_encode($set)), 0, 5);
            $tags = separateTag('post.list.' . $request->boardNo);
            $data = Cache::tags($tags)->remember($hash, config('cache.custom.expire.common'), function() use ($set, $whereModel) {
                $post = $whereModel->select($set['select'])
                                ->skip( ($set['page']-1) * $set['view'] )
                                ->take($set['view'])
                                ->get();

                return $post;
            });
        }

        // 게시판 정보 필요시
        if ( $boardInfoFlag ) {
        }


        $data = isset($data) ? $data->toArray() : array();

        return response()->json(['header' => $header, 'list' => $data], 200);
    }


}
