<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;
use Carbon\Carbon;


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
    public $attachType = 'post';

    /**
     *  @OA\Schema (
     *      schema="postCreate",
     *      required={"title", "content"},
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *      @OA\Property(property="thumbnail", type="integer", example=23, description="섬네일로 사용할 임시 이미지의 고유번호" )
     *  )
     */


    /**
     * @OA\Post(
     *      path="/v1/board/{boardId}/post",
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
     *                              @OA\Property(property="100021", ref="#/components/schemas/RequestResponse/properties/100021"),
     *                              @OA\Property(property="100022", ref="#/components/schemas/RequestResponse/properties/100022"),
     *                              @OA\Property(property="100041", ref="#/components/schemas/RequestResponse/properties/100041"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="101001", ref="#/components/schemas/RequestResponse/properties/101001"),
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
        if ( !isset($request->boardId) ) {
            return response()->json(getResponseError(100001, 'boardId'), 422);
        }

        // 존재 하는 게시판인지 확인
        if ( ! intval($request->boardId) ) {
            return response()->json(getResponseError(100041, 'boardId'), 422);
        }

        // 게시판 정보
        $board = BoardController::funcGetBoard($request->boardId);
        if ( !$board ) {
            return response()->json(getResponseError(100022, 'boardId'), 422);
        }
        $board = $board->toArray();

        // 작성 가능 권한 체크
        if ( $board['options']['board'] == 'manager' && auth()->user()->grade != 100 ) {
            return response()->json(getResponseError(101001), 403);
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
                    break;

                // 첨부파일 **check**
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
        $post->board_id = $request->boardId;
        $post->user_id = auth()->user()->id;
        $post->title = $request->title;
        $post->content = $request->content;

        if ( count($etc) ) {
            $post->etc = $etc;
        }

        $post->save();

        // 섬네일 사용 게시판이고, 임시 섬네일이 있을경우 사용처로 이동
        if (isset($board['options']['thumbnail']) && $board['options']['thumbnail'] && $request->thumbnail) {
            $attachCtl = new AttachController;
            $attachCtl->move($this->attachType, $post->id, [$request->thumbnail], ['type' => 'thumbnail']);
        }

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.list'])->flush();

        return response()->json([
            'message' => __('common.created'),
            'data' => [
                'no' => $post->id
            ]
        ], 200);
    }


    /**
     *  @OA\Schema (
     *      schema="postModify",
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *      @OA\Property(property="thumbnail", type="integer", example=42, description="섬네일로 사용할 임시 이미지의 고유번호" ),
     *      @OA\Property(property="delFiles", type="array", example={23,52}, description="삭제할 파일의 고유번호",
     *          @OA\Items(type="integer" ),
     *      )
     *  )
     */

    /**
     * @OA\Patch(
     *      path="/v1/board/{boardId}/post/{id}",
     *      summary="게시판 글 수정",
     *      description="게시판 글 수정",
     *      operationId="postModify",
     *      tags={"게시판 글"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/postModify"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="수정되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="수정되었습니다." ),
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
     *                              @OA\Property(property="100021", ref="#/components/schemas/RequestResponse/properties/100021"),
     *                              @OA\Property(property="100022", ref="#/components/schemas/RequestResponse/properties/100022"),
     *                              @OA\Property(property="100041", ref="#/components/schemas/RequestResponse/properties/100041"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="101001", ref="#/components/schemas/RequestResponse/properties/101001"),
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
     * @param ModifyPostsRequest $request
     * @return mixed
     */
    public function modify(ModifyPostsRequest $request) {

        $post = Post::where(['id' => $request->id, 'board_id' => $request->boardId])->first();

        if ( !$post ) {
            return response()->json(getResponseError(100005), 422);
        }

        // 게시글 정보
        $postInfo = $post->toArray();

        // 작성자와 동일 체크
        if ( $postInfo['userId'] != auth()->user()->id ) {
            return response()->json(getResponseError(101001), 422);
        }

        // 이미 삭제된 게시글 일 경우
        if ( $postInfo['del'] ) {
            return response()->json(getResponseError(200003), 422);
        }

        $boardInfo = Board::find($request->boardId)->toArray();
//        print_r($request->all());
//        print_r($boardInfo);
        $attachFlag = false;

        $flushFlag = false;
        $uptArrs = [];

        // 제목 수정
        if ( isset($request->title) ) {
            $uptArrs['title'] = $request->title;
        }

        // 내용 수정
        if ( isset($request->content) ) {
            $uptArrs['content'] = $request->content;
        }

        foreach ( $boardInfo['options'] as $type => $val ) {
            switch ($type) {
                // 섬네일
                case 'thumbnail':
                    if ($request->thumbnail) {
                        $attachCtl = new AttachController;
                        $attachCtl->move($this->attachType, $request->id, [$request->thumbnail], ['type' => 'thumbnail']);
                    }

                case 'attachFile':
                    $attachFlag = true;
                    break;
            }
        }

        // 삭제할 파일
        if ( $attachFlag && isset($request->delFiles) && is_array($request->delFiles) ) {
            $attachCtl = new AttachController;
            $attachCtl->directDelete($request->delFiles);

            $flushFlag = true;
        }

        // 변경사항이 있을 경우
        if ( count($uptArrs) ) {
            $post->update($uptArrs);
            $flushFlag = true;
        }

        // 캐시 초기화
        if ($flushFlag) {
            Cache::tags(['board.' . $request->boardId . '.post.' . $request->id])->forget('info');  // 상세 정보 캐시 삭제
            Cache::tags(['board.' . $request->boardId . '.post.list'])->flush();                    // 상세 목록 캐시 flush
        }

        return response()->json([
            'message' => __('common.modified')
        ], 200);
    }


    /**
     * @OA\delete(
     *      path="/v1/board/{boardId}/post/{id}",
     *      summary="게시판 글 삭제",
     *      description="게시판 글 삭제",
     *      operationId="postDelete",
     *      tags={"게시판 글"},
     *      @OA\Response(
     *          response=200,
     *          description="삭제되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="삭제되었습니다." ),
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
     *                              @OA\Property(property="100005", ref="#/components/schemas/RequestResponse/properties/100005"),
     *                              @OA\Property(property="101001", ref="#/components/schemas/RequestResponse/properties/101001"),
     *                              @OA\Property(property="200003", ref="#/components/schemas/RequestResponse/properties/200003"),
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
     * @param DeletePostsRequest $request
     * @return mixed
     */
    public function delete(DeletePostsRequest $request) {
        $post = Post::where(['id' => $request->id, 'board_id' => $request->boardId])->first();

        if ( !$post ) {
            return response()->json(getResponseError(100005), 422);
        }

        // 게시글 정보
        $postInfo = $post->toArray();

        // 이미 삭제처리 되어있는 경우
        if ( $postInfo['del'] ) {
            return response()->json(getResponseError(200003), 422);
        }

        // 작성자와 동일 체크
        if ( $postInfo['userId'] != auth()->user()->id ) {
            return response()->json(getResponseError(101001), 422);
        }

        // 논리적 삭제 진행
        $post->del = 1;
        $post->save();

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.' . $request->id])->flush();               // 상세 정보 캐시 삭제
        Cache::tags(['board.' . $request->boardId . '.post.list'])->flush();

        return response()->json([
            'message' => __('common.deleted')
        ], 200);
    }


    /**
     * @OA\Schema (
     *      schema="postList",
     *      @OA\Property(property="boardInfo", type="integer", example=1, default=0, description="게시판 정보 출력 여부<br/>0 : 미출력<br/>1 : 출력" ),
     *      @OA\Property(property="page", type="integer", example=1, default=1, description="게시글 페이지" ),
     *      @OA\Property(property="perPage", type="integer", example=15, description="한 페이지당 보여질 갯 수" )
     * )
     *
     * @OA\Get(
     *      path="/v1/board/{boardId}/post",
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
     *                      @OA\Property(property="thumbnail", type="string", example="http://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="게시글 섬네일 이미지 url" ),
     *                      @OA\Property(property="replyCount", type="integer", example=20, description="게시글의 댓글 수" ),
     *                      @OA\Property(property="userName", type="string", example="홍길동", description="작성자" ),
     *                      @OA\Property(property="boardId", type="integer", example=1, description="게시판 고유번호" ),
     *                      @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
     *                      @OA\Property(property="regDate", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *                      @OA\Property(property="uptDate", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
     *                  )
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed get lists",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100022", ref="#/components/schemas/RequestResponse/properties/100022"),
     *                              @OA\Property(property="100051", ref="#/components/schemas/RequestResponse/properties/100051"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="100081", ref="#/components/schemas/RequestResponse/properties/100081"),
     *                              @OA\Property(property="110001", ref="#/components/schemas/RequestResponse/properties/110001"),
     *                              @OA\Property(property="101001", ref="#/components/schemas/RequestResponse/properties/101001"),
     *                          ),
     *                      }
     *                  ),
     *              )
     *          )
     *      ),
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
        $board = BoardController::funcGetBoard($request->boardId);
        if ( !$board ) {
            return response()->json(getResponseError(100022, 'boardId'), 422);
        }
        $board = $board->toArray();

        // 게시글 목록
        $set = [
            'boardId' => $request->boardId,
            'boardInfo' => $boardInfoFlag,
            'page' => $request->page,
            'view' => $request->perPage,
            'select' => ['id', 'title', 'boardId', 'userId', 'regDate', 'uptDate']
        ];

        // where 절 eloquent
        $whereModel = Post::where(['board_id' => $set['boardId'], 'del' => 0]);

        // 섬네일 기능 사용시
        if ( isset($board['options']['thumbnail']) && $board['options']['thumbnail'] ) {
            $set['thumbnail'] = true;
            $set['select'][] = 'af.url AS thumbnail';
        }

        // 글 상태 사용시
        if ( isset($board['options']['boardStatus']) && $board['options']['boardStatus'] ) {
            $set['select'][] = 'etc';
        }

        // 시크릿 기능 사용시
        if ( isset($board['options']['secret']) && $board['options']['secret'] ) {
            if ( !auth()->user() ) {
                return response()->json(getResponseError(110001), 422);
            }

            $whereModel = $whereModel->where(['user_id' => auth()->user()->id]);
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
            return response()->json(getResponseError(101001), 422);
        }

        if ( $set['page'] <= $pagination['totalPage'] ) {
            // 데이터 cache
            $hash = substr(md5(json_encode($set)), 0, 5);
            $tags = separateTag('board.' . $set['boardId'] . '.post.list');

            $data = Cache::tags($tags)->get($hash);

            if ( is_null($data) ||
                (isset($data) && checkCacheStampede($data[1]->getPreciseTimestamp(3))) ) {

                $post = $whereModel
                    ->with('user:id,name')
                    ->select($set['select']);

                // 섬네일 사용시
                if ( isset($set['thumbnail']) && $set['thumbnail'] ) {
                    $post = $post->leftjoin('attach_files AS af', function($join){
                        $join
                            ->on('posts.id', '=', 'af.type_id')
                            ->where('af.type', $this->attachType)
                            ->whereJsonContains('af.etc', ['type' => 'thumbnail']);
                    });
                }

                $post = $post
                    ->groupBy('posts.id')
                    ->skip($pagination['skip'])
                    ->take($pagination['perPage'])
                    ->orderBy('id', 'desc');

                $post = $post->get();

                // 데이터 가공
//                $post->pluck('user')->each->setAppends([]);

                foreach ($post as $index) {
                    // 댓글 사용시
                    if ( isset($set['reply']) && $set['reply'] ) {
                        $replys = $index->replyCount;
                        unset($index->replyCount);
                        $index->replyCount = $replys->pluck('count')->toArray()[0];
                    }

                    // 유저 이름
                    if ($index->user) {
                        $index->userName = $index->user->toArray()['name'];
                    }
                    unset($index->user);
                }

                $data = [$post, Carbon::now()->addSeconds(config('cache.custom.expire.common'))];

                Cache::tags($tags)->put($hash, $data, config('cache.custom.expire.common'));
            }
        }


        $data = isset($data[0]) ? $data[0]->toArray() : array();

        $result = ['header' => $pagination];

        // 게시판 정보 필요시
        if ( $boardInfoFlag ) {
            $result['board'] = $board;
        }

        $result['list'] = $data;

        return response()->json($result, 200);
    }



    /**
     * @OA\Get(
     *      path="/v1/board/{boardId}/post/{id}",
     *      summary="게시판 글 상세",
     *      description="게시판 글 상세",
     *      operationId="postGetInfo",
     *      tags={"게시판 글"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully Created",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
     *              @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
     *              @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *              @OA\Property(property="hidden", type="integer", example=0, default=0, description="게시글 숨김 여부<br/>0:노출<br/>1:숨김" ),
     *              @OA\Property(property="del", type="integer", example=0, default=0, description="게시글 삭제 여부<br/>0:노출<br/>1:삭제" ),
     *              @OA\Property(property="etc", type="object", description="게시글 기타정보",
     *                  @OA\Property(property="status", type="string", example="wait", description="게시글 상태<br/>wait:접수<br/>ing:확인중<br/>end:답변완료" )
     *              ),
     *              @OA\Property(property="thumbnail", type="object", description="게시글 섬네일 이미지 정보",
     *                  @OA\Property(property="id", type="integer", example=4, description="이미지 고유 번호" ),
     *                  @OA\Property(property="url", type="string", example="http://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="이미지 url" ),
     *              ),
     *              @OA\Property(property="status", type="string", example="접수", description="게시글 상태" ),
     *              @OA\Property(property="userName", type="string", example="홍길동", description="작성자" ),
     *              @OA\Property(property="boardId", type="integer", example=1, description="게시판 고유번호" ),
     *              @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
     *              @OA\Property(property="regDate", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *              @OA\Property(property="uptDate", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed get lists",
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
     *                              @OA\Property(property="200003", ref="#/components/schemas/RequestResponse/properties/200003"),
     *                              @OA\Property(property="200004", ref="#/components/schemas/RequestResponse/properties/200004"),
     *                          ),
     *                      }
     *                  ),
     *              )
     *          )
     *      ),
     *  )
     */

    /**
     * @param Request $request
     * @return mixed
     */
    public function getInfo(Request $request) {

        $post = Post::where(['id' => $request->id, 'board_id' => $request->boardId])->exists();

        // 잘못된 정보입니다.
        if ( !$post ) {
            return response()->json(getResponseError(100005), 422);
        }

        // 게시글 정보
        $data = $this->funcGetInfo($request->id, $request->boardId);
        $postInfo = $data->toArray();

        // 이미 삭제된 게시글 일 경우
        if ( $postInfo['del'] ) {
            return response()->json(getResponseError(200003), 422);
        }

        // 이미 숨김 처리된 게시글 일 경우
        if ( $postInfo['hidden'] ) {
            return response()->json(getResponseError(200004), 422);
        }

        return response()->json($postInfo, 200);
    }


    /**
     * @param $postId
     * @param $boardId
     * @return mixed
     */
    public function funcGetInfo($postId, $boardId = 0) {
        // 게시판 번호가 없을 경우
        if ( !$boardId ) {
            $boardId = Post::select('boardId')->where('id', $postId)->first()['boardId'];
        }

        // 게시판 정보
        $board = BoardController::funcGetBoard($boardId);
        if ( !$board ) {
            return getResponseError(100022, 'boardId');
        }

        $boardInfo = $board->toArray();

        // 데이터 cache
        $tags = separateTag('board.' . $boardId . '.post.' . $postId);
        $data = Cache::tags($tags)->remember('info', config('cache.custom.expire.common'), function() use ($postId, $boardId, $boardInfo) {
            $select = ['id', 'title', 'boardId', 'content', 'hidden', 'del', 'etc', 'userId', 'regDate', 'uptDate'];

            // 섬네일 지원 게시판일 경우
            if ( $boardInfo['options']['thumbnail'] ) {
                $select[] = 'af.url AS thumbnail';
                $select[] = 'af.id AS thumbNo';
            }

            // 게시글 답변 지원 게시판 일 경우
            if ( $boardInfo['options']['boardReply'] ) {
                $select[] = 'comment';
            }

            $post = Post::select($select)->where(['posts.id' => $postId, 'board_id' => $boardId]);

            // 섬네일 사용
            if ( $boardInfo['options']['thumbnail'] ) {
                $post = $post->leftjoin('attach_files AS af', function($join){
                    $join
                        ->on('posts.id', '=', 'af.type_id')
                        ->where('type', $this->attachType)
                        ->whereJsonContains('af.etc', ['type' => 'thumbnail']);
                });
            }

            $post = $post->first();

            $post->thumbnail = [
                'id' => $post->thumbNo,
                'url' => $post->thumbnail
            ];
            unset($post->thumbNo);

            // 기타정보 가공
            if ( isset($post->etc['status']) ) {
                $post->status = __('common.post.etc.status.' . $post->etc['status']);
            }

            // 게시글 추가 정보 (회원)
            $post->userName = $post->user->toArray()['name'];
            unset($post->user);

            return $post;
        });

        return $data;

    }

    public function test(Request $request) {

//        Cache::tags(['people', 'artists'])->put('John', 'jone');
//        Cache::tags(['people', 'authors'])->put('Anne', 'anne');
//
//        var_dump(Cache::tags(['people', 'authors'])->get('Anne'));
//        Cache::tags(['board'])->put();


//        $aaa = \App\Models\Test::get();
//        print_r($aaa[0]->toArray());
    }

}
