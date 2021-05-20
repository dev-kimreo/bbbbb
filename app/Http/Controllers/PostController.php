<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;
use Carbon\Carbon;
use Str;

use App\Models\Post;
use App\Models\Board;

use App\Http\Requests\Posts\CreateRequest;
use App\Http\Requests\Posts\UpdateRequest;
use App\Http\Requests\Posts\DestroyRequest;
use App\Http\Requests\Posts\IndexRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\PaginationLibrary;
use App\Libraries\CollectionLibrary;

use App\Services\BoardService;
use App\Services\PostService;
use App\Services\AttachService;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class PostController extends Controller
{
    public function __construct(Post $post, Board $board, BoardService $boardService, PostService $postService, AttachService $attachService)
    {
        $this->post = $post;
        $this->board = $board;
        $this->boardService = $boardService;
        $this->postService = $postService;
        $this->attachService = $attachService;
    }

    /**
     * @OA\Schema (
     *      schema="postCreate",
     *      required={"title", "content"},
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" )
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
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="생성되었습니다." ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * 게시글 작성
     * @param CreateRequest $request
     * @return mixed
     */
    public function create(CreateRequest $request)
    {
        // 게시판 정보
        $boardCollect = $this->boardService->getInfo($request->boardId);

        // check write post Policy
        if (!auth()->user()->can('create', [$this->post, $boardCollect])) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 게시글 작성
        $this->post->board_id = $request->boardId;
        $this->post->user_id = auth()->user()->id;
        $this->post->title = $request->title;
        $this->post->content = $request->content;

        $this->post->save();

        $this->post->refresh();

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.list'])->flush();

        return response()->json(CollectionLibrary::toCamelCase(collect($this->post)), 201);
    }


    /**
     * @OA\Schema (
     *      schema="postModify",
     *      @OA\Property(property="title", type="string", example="게시글 입니다.", description="게시글 제목" ),
     *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" )
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
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="수정되었습니다." ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * @param UpdateRequest $request
     * @return mixed
     */
    public function modify(UpdateRequest $request)
    {
        $this->post = $this->post->getByBoardId($request->id, $request->boardId)->first();

        if (!$this->post) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        // check update post Policy
        if (!auth()->user()->can('update', $this->post)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $flushFlag = false;
        $uptArrs = [];

        // 제목 수정
        if (isset($request->title)) {
            $uptArrs['title'] = $request->title;
        }

        // 내용 수정
        if (isset($request->content)) {
            $uptArrs['content'] = $request->content;
        }

        // 변경사항이 있을 경우
        if (count($uptArrs)) {
            $this->post->update($uptArrs);
            $flushFlag = true;
        }

        // 캐시 초기화
        if ($flushFlag) {
            Cache::tags(['board.' . $request->boardId . '.post.' . $request->id])->flush();  // 상세 정보 캐시 삭제
            Cache::tags(['board.' . $request->boardId . '.post.list'])->flush();                    // 상세 목록 캐시 flush
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($this->post)), 201);
    }


    /**
     * @OA\delete(
     *      path="/v1/board/{boardId}/post/{id}",
     *      summary="게시판 글 삭제",
     *      description="게시판 글 삭제",
     *      operationId="postDelete",
     *      tags={"게시판 글"},
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
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * @param DestroyRequest $request
     * @return mixed
     */
    public function delete(DestroyRequest $request)
    {
        $postCollect = $this->post->where(['id' => $request->id, 'board_id' => $request->boardId])->first();

        if (!$postCollect) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        // check update post Policy
        if (!auth()->user()->can('delete', $postCollect)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 첨부파일 삭제
        $this->attachService->delete($postCollect->attachFiles->modelKeys());

        // 소프트 삭제 진행
        $postCollect->delete();

        // 캐시 초기화
        Cache::tags(['board.' . $request->boardId . '.post.' . $request->id])->flush();               // 상세 정보 캐시 삭제
        Cache::tags(['board.' . $request->boardId . '.post.list'])->flush();

        return response()->noContent();
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
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
     *                      @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
     *                      @OA\Property(property="thumbnail", type="object",
     *                          @OA\Property(property="url", example="http://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="게시글 섬네일 이미지 url" ),
     *                      ),
     *                      @OA\Property(property="replyCount", type="integer", example=20, description="게시글의 댓글 수" ),
     *                      @OA\Property(property="userName", type="string", example="홍길동", description="작성자" ),
     *                      @OA\Property(property="boardId", type="integer", example=1, description="게시판 고유번호" ),
     *                      @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
     *                      @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *                      @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
     *                  )
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     */
    /**
     * @param IndexRequest $request
     * @return mixed
     */
    public function getList(IndexRequest $request)
    {
        // init
        $boardInfoFlag = isset($request->boardInfo) ? $request->boardInfo : 0;

        // 게시판 정보
        $boardCollect = $this->boardService->getInfo($request->boardId);
        $board = $boardCollect->toArray();

        // 게시글 목록
        $set = [
            'boardId' => $request->boardId,
            'boardInfo' => $boardInfoFlag,
            'page' => $request->page,
            'view' => $request->perPage,
            'select' => ['posts.id', 'title', 'board_id', 'posts.user_id', 'posts.created_at', 'posts.updated_at']
        ];

        // where 절 eloquent
        $whereModel = $this->post->where(['board_id' => $set['boardId']]);

        // 섬네일 기능 사용시
        if (isset($boardCollect->options['thumbnail']) && $boardCollect->options['thumbnail']) {
            $set['thumbnail'] = true;
        }

        // 시크릿 기능 사용시
        if (isset($boardCollect->options['secret']) && $boardCollect->options['secret']) {
            if (!auth()->user()) {
                throw new QpickHttpException(401, 'common.required_login');
            }

            $whereModel = $whereModel->where(['user_id' => auth()->user()->id]);
        }

        // 댓글 사용시
        if ($boardCollect->options['reply']) {
            $set['reply'] = true;
        }

        // 파일 첨부 **check**
        if (isset($boardCollect->options['attachFile']) && $boardCollect->options['attachFile']) {

        }

        // pagination
        $pagination = PaginationLibrary::set($set['page'], $whereModel->count(), $set['view']);

        if ($set['page'] <= $pagination['totalPage']) {
            // 데이터 cache
            $hash = substr(md5(json_encode($set)), 0, 5);
            $tags = separateTag('board.' . $set['boardId'] . '.post.list');

            $data = Cache::tags($tags)->get($hash);

            if (is_null($data) ||
                (isset($data) && checkCacheStampede($data[1]->getPreciseTimestamp(3)))) {

                $post = $whereModel
                    ->with('user:id,name')
                    ->select($set['select']);

                // 섬네일 사용시
                if (isset($set['thumbnail']) && $set['thumbnail']) {
                    $post = $post
                        ->with(['attachFiles' => function ($query) {
                            $query->select('url', 'attachable_id', 'attachable_type')->whereJsonContains('etc', ['type' => 'thumbnail']);
                        }]);
                }

                $post = $post
                    ->groupBy('posts.id')
                    ->skip($pagination['skip'])
                    ->take($pagination['perPage'])
                    ->orderBy('sort', 'asc')
                    ->orderBy('id', 'desc');

                $post = $post->get();


                // 데이터 가공
                $post->each(function (&$v) use ($set) {

                    // 유저 이름
                    if ($v->user) {
                        $v->userName = $v->user->toArray()['name'];
                        unset($v->user);
                    }

                    // 섬네일 있을 경우
                    if ($v->attachFiles) {
                        $v->attachFiles->each(function (&$v2) {
                            unset($v2->attachable_id, $v2->attachable_type);
                        });
                        $v->thumbnail = $v->attachFiles;
                        unset($v->attachFiles);
                    }

                    // 댓글 사용시
                    if (isset($set['reply']) && $set['reply']) {
                        $replys = $v->replyCount;
                        unset($v->replyCount);
                        $v->replyCount = $replys->pluck('count')->toArray()[0];
                    }
                });

                $data = [$post, Carbon::now()->addSeconds(config('cache.custom.expire.common'))];

                Cache::tags($tags)->put($hash, $data, config('cache.custom.expire.common'));
            }
        }

        $data = isset($data[0]) ? $data[0]->toArray() : array();

        $result = ['header' => $pagination];

        // 게시판 정보 필요시
        if ($boardInfoFlag) {
            $result['board'] = $boardCollect;
        }

        $result['list'] = $data;

        return response()->json(CollectionLibrary::toCamelCase(collect($result)), 200);
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
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
     *              @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
     *              @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
     *              @OA\Property(property="hidden", type="integer", example=0, default=0, description="게시글 숨김 여부<br/>0:노출<br/>1:숨김" ),
     *              @OA\Property(property="thumbnail", type="object", description="게시글 섬네일 이미지 정보",
     *                  @OA\Property(property="id", type="integer", example=4, description="이미지 고유 번호" ),
     *                  @OA\Property(property="url", type="string", example="http://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="이미지 url" ),
     *              ),
     *              @OA\Property(property="userName", type="string", example="홍길동", description="작성자" ),
     *              @OA\Property(property="boardId", type="integer", example=1, description="게시판 고유번호" ),
     *              @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
     *              @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
     *              @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     */

    /**
     * @param Request $request
     * @return mixed
     */
    public function getInfo(Request $request)
    {

        $post = $this->post->where(['id' => $request->id, 'board_id' => $request->boardId])->exists();

        // 잘못된 정보입니다.
        if (!$post) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        // 게시글 정보
        $postCollect = $this->postService->getInfo($request->id);

        // 이미 숨김 처리된 게시글 일 경우
        if ($postCollect->hidden) {
            throw new QpickHttpException(403, 'post.disable.hidden');
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($postCollect)), 200);
    }


}
