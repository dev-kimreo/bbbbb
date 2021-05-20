<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;
use Carbon\Carbon;

use App\Models\Inquiry;

use App\Http\Requests\Inquiries\CreateRequest;
use App\Http\Requests\Inquiries\IndexRequest;
use App\Http\Requests\Inquiries\ShowRequest;
use App\Http\Requests\Inquiries\UpdateRequest;
use App\Http\Requests\Inquiries\DestroyRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\PaginationLibrary;
use App\Libraries\CollectionLibrary;

use App\Services\AttachService;

/**
 * Class PostController
 * @package App\Http\Controllers
 */
class InquiryController extends Controller
{
    private Inquiry $inquiry;
    private AttachService $attachService;

    public function __construct(Inquiry $inquiry, AttachService $attachService)
    {
        $this->inquiry = $inquiry;
        $this->attachService = $attachService;
    }



    /**
     * @OA\Schema (
     *      schema="inquiryCreate",
     *      required={"title", "question"},
     *      @OA\Property(property="title", type="string", example="1:1 문의 제목입니다.", description="1:1 문의 제목" ),
     *      @OA\Property(property="question", type="string", example="1:1 문의 내용입니다.", description="1:! 문의 내용" )
     *  )
     */


    /**
     * @OA\Post(
     *      path="/v1/inquiry",
     *      summary="1:1문의 작성",
     *      description="1:1문의 작성",
     *      operationId="inquiryCreate",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/inquiryCreate"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Inquiry"
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
    public function store(CreateRequest $request)
    {
        // Bacckoffice login
        if (auth()->user()->isLoginToManagerService()) {

        } else {
            // check write Policy
            if (!auth()->user()->can('create', [$this->inquiry])) {
                throw new QpickHttpException(403, 'common.unauthorized');
            }

            // 데이터 가공
            $this->inquiry->user_id = auth()->user()->id;
            $this->inquiry->title = $request->title;
            $this->inquiry->question = $request->question;
            $this->inquiry->created_at = Carbon::now();
            $this->inquiry->save();

            $this->inquiry->refresh();

            return response()->json(CollectionLibrary::toCamelCase(collect($this->inquiry)), 201);
        }
    }


    /**
     * @OA\Schema (
     *      schema="inquiryList",
     *      @OA\Property(property="page", type="integer", example=1, default=1, description="페이지" ),
     *      @OA\Property(property="perPage", type="integer", example=15, description="한 페이지당 보여질 갯 수" )
     * )
     *
     * @OA\Get(
     *      path="/v1/inquiry",
     *      summary="1:1문의 목록",
     *      description="1:1문의 목록",
     *      operationId="inquiryGetList",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/inquiryList"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      ref="#/components/schemas/Inquiry"
     *                  )
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{},
     *          "admin_auth":{}
     *      }}
     *  )
     */
    public function index(IndexRequest $request)
    {
        //  목록
        $set = [
            'page' => $request->page,
            'view' => $request->perPage
        ];

        // Bacckoffice login
        if (auth()->user()->isLoginToManagerService()) {

        } else {

            // check viewAny Policy
            if (!auth()->user()->can('viewAny', [$this->inquiry])) {
                throw new QpickHttpException(403, 'common.unauthorized');
            }

            // where 절
            $whereModel = $this->inquiry->where(['user_id' => auth()->user()->id]);

            // pagination
            $pagination = PaginationLibrary::set($set['page'], $whereModel->count(), $set['view']);

            // 문의 정보
            $inquiry =
                $whereModel
                    ->with('user')
                    ->skip($pagination['skip'])
                    ->take($pagination['perPage'])
                    ->orderBy('id', 'desc');

            $data = $inquiry->get();


            // 데이터 가공
            $data->each(function (&$v) use ($set) {
                // 유저 이름
                if ($v->user) {
                    $v->userName = $v->user->toArray()['name'];
                    unset($v->user);
                }
            });
        }

        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return response()->json(CollectionLibrary::toCamelCase(collect($result)), 200);
    }


    /**
     * @OA\Get(
     *      path="/v1/inquiry/{id}",
     *      summary="1:1문의 상세",
     *      description="1:1문의 상세",
     *      operationId="inquiryGetInfo",
     *      tags={"1:1문의"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              allOf={
     *                   @OA\Schema(ref="#/components/schemas/Inquiry"),
     *                   @OA\Schema(
     *                      @OA\Property(property="attachFiles", type="array",
     *                          @OA\Items(ref="#/components/schemas/AttachFile")
     *                      )
     *                   )
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{},
     *          "admin_auth":{}
     *      }}
     *  )
     */
    public function show($id, ShowRequest $request)
    {
        // Bacckoffice login
        if (auth()->user()->isLoginToManagerService()) {

        } else {
            // 문의 정보
            $collect = $this->inquiry
                ->with('answer')
                ->with('attachFiles', function ($q) {
                    return $q->select('id', 'url', 'attachable_id', 'attachable_type');
                })
                ->where([
                    'id' => $id,
                    'user_id' => auth()->user()->id
                ])
                ->first();
            if (!$collect) {
                throw new QpickHttpException(404, 'common.not_found');
            }

            // check view Policy
            if (!auth()->user()->can('view', [$collect])) {
                throw new QpickHttpException(403, 'common.unauthorized');
            }

            return response()->json(CollectionLibrary::toCamelCase(collect($collect)), 200);
        }
    }


    /**
     * @OA\Patch(
     *      path="/v1/inquiry/{id}",
     *      summary="1:1문의 수정",
     *      description="1:1문의 수정",
     *      operationId="inquiryModify",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/inquiryCreate"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Inquiry"
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
    public function update($id, UpdateRequest $request)
    {
        // Bacckoffice login
        if (auth()->user()->isLoginToManagerService()) {

        } else {
            // 문의 정보
            $collect = $this->inquiry
                ->where(['id' => $id, 'user_id' => auth()->user()->id])
                ->first();
            if (!$collect) {
                throw new QpickHttpException(404, 'common.not_found');
            }

            if (!auth()->user()->can('update', [$collect])) {
                throw new QpickHttpException(403, 'common.unauthorized');
            }

            $collect->title = $request->title ?? $collect->title;
            $collect->question = $request->question ?? $collect->question;

            // 수정
            if ($collect->isDirty()) {
                $collect->updated_at = Carbon::now();
                $collect->save();
            }

            return response()->json(CollectionLibrary::toCamelCase(collect($collect)), 201);
        }
    }


    /**
     * @OA\delete(
     *      path="/v1/inquiry/{id}",
     *      summary="1:1문의 삭제",
     *      description="1:1문의 삭제",
     *      operationId="inquiryDelete",
     *      tags={"1:1문의"},
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
    public function destroy($id, DestroyRequest $request)
    {
        // Bacckoffice login
        if (auth()->user()->isLoginToManagerService()) {

        } else {
            // 문의 정보
            $collect = $this->inquiry
                ->where([
                    'id' => $id,
                    'user_id' => auth()->user()->id
                ])
                ->first();
            if (!$collect) {
                throw new QpickHttpException(404, 'common.not_found');
            }

            if (!auth()->user()->can('delete', [$collect])) {
                throw new QpickHttpException(403, 'common.unauthorized');
            }

            // 첨부파일 삭제
            $this->attachService->delete($collect->attachFiles->modelKeys());

            // 문의 소프트 삭제
            $collect->delete();

            return response()->noContent();
        }
    }


}
