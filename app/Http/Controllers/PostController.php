<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Closure;
use Cache;


use App\Http\Controllers\BoardController;
use App\Http\Controllers\AttachController;

use App\Models\Post;
use App\Models\Board;
use App\Models\Reply;
use App\Models\AttachFile;

use App\Http\Requests\Posts\GetListPostsRequest;
use App\Http\Requests\Posts\CreatePostsRequest;
use App\Http\Requests\Posts\ModifyPostsRequest;
use App\Http\Requests\Posts\DeletePostsRequest;

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
     *      path="/v1/board/{boardNo}/post",
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
        // 필수 파라미터 확인
        if ( !isset($request->boardNo) ) {
            return response()->json(getResponseError(100001, 'boardNo'), 422);
        }

        // 존재 하는 게시판인지 확인
        if ( ! intval($request->boardNo) ) {
            return response()->json(getResponseError(100041, 'boardNo'), 422);
        }

        // 게시판 정보
        $board = BoardController::funcGetBoard($request->boardNo);
        if ( !$board ) {
            return response()->json(getResponseError(100022, 'boardNo'), 422);
        }
        $board = $board->toArray();

        // 작성 가능 권한 체크
        if ( $board['options']['board'] == 'manager' && auth()->user()->grade != 100 ) {
            return response()->json(getResponseError(101001), 422);
        }

        /**
         * 게시글 작성 데이터
         */
        $etc = [];

        // 후처리
        $after['thumbnail'] = null;

        foreach ( $board['options'] as $type => $val ) {
            switch ($type) {
                // 섬네일
                case 'thumbnail':
                    if ( $request->thumbnail ) {
                        $fileInfo = AttachFile::where(['id' => $request->thumbnail, 'user_no' => auth()->user()->id, 'type' => 'temp'])->first();
                        $after['thumbnail'] = $fileInfo->url;
                    }
                    break;

                // 첨부파일
                case 'attachFile':
                    break;

                // 게시글 상태 사용
                case 'boardStatus':
                    if ( isset($val) && $val ) {
                        $etc['status'] = 'wait';
                    }
                    break;
            }
        }

        // 게시글 작성
        $post = New Post;
        $post->board_no = $request->boardNo;
        $post->user_no = auth()->user()->id;
        $post->title = $request->title;
        $post->content = $request->content;

        if ( count($etc) ) {
            $post->etc = $etc;
        }

        $post->save();

        // 임시 섬네일 이동
        if ( !is_null($after['thumbnail']) ) {
            $attachCtl = new AttachController;
            $attachCtl->move('postThumb', $post->id, [
                $after['thumbnail']
            ]);
        }

        return response()->json([
            'message' => __('common.created'),
            'data' => [
                'no' => $post->id
            ]
        ], 200);
    }


    /**
     * @param ModifyPostsRequest $request
     * @return mixed
     */
    public function modify(ModifyPostsRequest $request) {

        $post = Post::where(['id' => $request->id, 'board_no' => $request->boardNo])->first();

        if ( !$post ) {
            return response()->json(getResponseError(100005), 422);
        }

        // 게시글 정보
        $postInfo = $post->toArray();

        // 작성자와 동일 체크
        if ( $postInfo['userNo'] != auth()->user()->id ) {
            return response()->json(getResponseError(101001), 422);
        }

        // 이미 삭제된 게시글 일 경우
        if ( $postInfo['del'] ) {
            return response()->json(getResponseError(200003), 422);
        }

        $boardInfo = Board::find($request->boardNo)->toArray();

        // 데이터 수정
        if ( isset($request->title) ) {
            $post->title = $request->title;
        }

        if ( isset($request->content) ) {
            $post->content = $request->content;
        }

        $post->save();

        return response()->json([
            'message' => __('common.modified')
        ], 200);

//        foreach ( $boardInfo['options'] as $type => $val ) {
//            switch ($type) {
//                // 섬네일
//                case 'thumbnail':
//                    break;
//
//                // 첨부파일
//                case 'attachFile':
//                    break;
//
//                // 게시글 상태 사용
//                case 'boardStatus':
////                    if ( isset($val) && $val ) {
////                        $etc['status'] = 'wait';
////                    }
//                    break;
//            }
//        }


    }


    /**
     * @param DeletePostsRequest $request
     * @return mixed
     */
    public function delete(DeletePostsRequest $request) {
        $post = Post::where(['id' => $request->id, 'board_no' => $request->boardNo])->first();

        if ( !$post ) {
            return response()->json(getResponseError(100005), 422);
        }

        // 게시글 정보
        $postInfo = $post->toArray();

        // 작성자와 동일 체크
        if ( $postInfo['userNo'] != auth()->user()->id ) {
            return response()->json(getResponseError(101001), 422);
        }

        // 논리적 삭제 진행
        $post->del = 1;
        $post->save();

        return response()->json([
            'message' => __('common.deleted')
        ], 200);
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
            'select' => ['id', 'title', 'comment', 'boardNo', 'userNo', 'regDate', 'uptDate', 'af.url AS thumbnail']
        ];

        // where 절 eloquent
        $whereModel = Post::where(['board_no' => $set['boardNo']]);

        // 섬네일 기능 사용시
        if ( isset($board['options']['thumbnail']) && $board['options']['thumbnail'] ) {
            $set['thumbnail'] = true;
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

        // 댓글 사용시
        if ( $board['options']['reply'] ) {
            $set['reply'] = true;
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
            // 데이터 cache
            $hash = substr(md5(json_encode($set)), 0, 5);
            $tags = separateTag('post.list.' . $set['boardNo']);
            $data = Cache::tags($tags)->remember($hash, config('cache.custom.expire.common'), function() use ($set, $pagination, $whereModel) {
                $post = $whereModel
                        ->with('user:id,name')
                        ->select($set['select']);

                // 섬네일 사용시
                if ( isset($set['thumbnail']) && $set['thumbnail'] ) {
                    $post = $post->leftjoin('attach_files AS af', function($join){
                        $join
                            ->on('posts.id', '=', 'af.type_no')
                            ->where('type', 'postThumb');
                    });
                }

                $post = $post
                        ->skip($pagination['skip'])
                        ->take($pagination['perPage'])
                        ->orderBy('id', 'desc');

//                var_dump($post->toSql());
                $post = $post->get();

                // 데이터 가공
                $post->pluck('user')->each->setAppends([]);
                foreach ($post as $index) {
                    // 댓글 사용시
                    if ( isset($set['reply']) && $set['reply'] ) {
                        $replys = $index->replyCount;
                        unset($index->replyCount);
                        $index->replyCount = $replys->pluck('count')->toArray()[0];
                    }

                    // 유저 이름
                    $index->userName = $index->user->toArray()['name'];
                    unset($index->user);
                }

//                $data = $post;
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


    /**
     * @param Request $request
     * @return mixed
     */
    public function getInfo(Request $request) {

        $post = Post::where(['id' => $request->id, 'board_no' => $request->boardNo])->exists();

        // 잘못된 정보입니다.
        if ( !$post ) {
            return response()->json(getResponseError(100005), 422);
        }

        // 게시판 정보
        $board = BoardController::funcGetBoard($request->boardNo);
        $boardInfo = $board->toArray();


        $tags = separateTag('post.info');
        $data = Cache::tags($tags)->remember($request->id, config('cache.custom.expire.common'), function() use ($request, $boardInfo) {
            $select = ['id', 'title', 'boardNo', 'content', 'hidden', 'del', 'etc', 'userNo', 'regDate', 'uptDate'];

            if ( $boardInfo['options']['thumbnail'] ) {
                $select[] = 'af.url AS thumbnail';
            }

            if ( $boardInfo['options']['boardReply'] ) {
                $select[] = 'comment';
            }

            $post = Post::select($select)->where(['posts.id' => $request->id, 'board_no' => $request->boardNo]);

            // 섬네일 사용
            if ( $boardInfo['options']['thumbnail'] ) {
                $post = $post->leftjoin('attach_files AS af', function($join){
                    $join
                        ->on('posts.id', '=', 'af.type_no')
                        ->where('type', 'postThumb');
                });
            }

            $post = $post->first();

            // 게시글 추가 정보 (회원)
            $post->userName = $post->user->toArray()['name'];
            unset($post->user);

            return $post;
        });

        // 게시글 정보
        $postInfo = $data->toArray();

        // 이미 삭제된 게시글 일 경우
        if ( $postInfo['del'] ) {
            return response()->json(getResponseError(200003), 422);
        }

        return response()->json($postInfo, 200);
    }

}
