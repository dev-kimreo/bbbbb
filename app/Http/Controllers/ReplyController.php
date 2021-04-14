<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;


use App\Http\Controllers\BoardController;

use App\Models\Post;
//use App\Models\Board;
//use App\Models\Reply;

//use App\Http\Requests\Posts\GetListPostsRequest;

use App\Libraries\PageCls;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class ReplyController extends Controller
{
    public $attachType = 'reply';

    /**
     *  @OA\Schema (
     *      schema="replyCreate",
     *      required={"title", "content"},
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *      @OA\Property(property="thumbnail", type="integer", example=23, description="섬네일로 사용할 임시 이미지의 고유번호" )
     *  )
     */


    /**
     * @OA\Post(
     *      path="/v1/post/{postNo}/reply",
     *      summary="게시판 글 작성",
     *      description="게시판 글 작성",
     *      operationId="postCreate",
     *      tags={"게시판 글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/replyCreate"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="생성되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="생성되었습니다." ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="실패",
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

    public function create(Request $request) {

        // 필수 파라미터 확인
        if ( !isset($request->postNo) ) {
            return response()->json(getResponseError(100001, 'postNo'), 422);
        }

        // 게시글 번호 확인
        if ( ! intval($request->postNo) ) {
            return response()->json(getResponseError(100041, 'postNo'), 422);
        }


        // 게시글 정보
        $boardNo = Post::select('boardNo')->where('id', $request->postNo)->first()['boardNo'];
        $board = BoardController::funcGetBoard($boardNo);

        if ( !$board ) {
            return response()->json(getResponseError(100022, 'boardNo'), 422);
        }
        $boardInfo = $board->toArray();
        if ( !$boardInfo['options']['reply'] ) {

        }


//
//        // 작성 가능 권한 체크
//        if ( $board['options']['board'] == 'manager' && auth()->user()->grade != 100 ) {
//            return response()->json(getResponseError(101001), 422);
//        }
    }



}
