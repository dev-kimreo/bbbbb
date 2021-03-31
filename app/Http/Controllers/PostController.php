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
     *      )
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

    public function getList(GetListPostsRequest $request) {
        // init
        $boardInfoFlag = isset($request->boardInfo) ? $request->boardInfo : 0;

        // 게시판 정보 필요시
        if ( $boardInfoFlag ) {
            $board = BoardController::funcGetBoard($request->boardNo);
            if ( !$board ) {
                return response()->json(getResponseError(10001, 'boardNo'), 422);
            }
        }

        // 게시글 목록
        $set = [
            'boardNo' => $request->boardNo,
            'page' => $request->page ? intval($request->page) : 1,
            'view' => $request->view ? intval($request->view) : 10,
        ];

        // cache
        $hash = substr(md5(json_encode($set)), 0, 5);
        $tags = separateTag('post.list.' . $request->boardNo);
        $data = Cache::tags($tags)->remember($hash, config('cache.custom.expire.common'), function() use ($set) {
            echo '캐시를 새로한다.';
            $post = Post::where(['board_no' => $set['boardNo']])->skip( ($set['page']-1) * $set['view'] )->take($set['view'])->get();

            return $post;
        });

        print_r($data->toArray());



    }


}
