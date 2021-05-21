<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
    private AttachService $attachService;

    public function __construct(Inquiry $inquiry, AttachService $attachService)
    {
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
    public function store(CreateRequest $request, Inquiry $inquiry)
    {
        // 데이터 가공
        $inquiry->timestamps = false;
        $inquiry->user_id = Auth::id();
        $inquiry->title = $request->title;
        $inquiry->question = $request->question;
        $inquiry->created_at = Carbon::now();
        $inquiry->save();
        $inquiry->refresh();

        return response()->json(CollectionLibrary::toCamelCase(collect($inquiry)), 201);
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
    public function index(IndexRequest $request): Collection
    {
        // check viewAny Policy
//        if (!auth()->user()->can('viewAny', [$this->inquiry])) {
//            throw new QpickHttpException(403, 'common.unauthorized');
//        }

        // Set Model
        $inquiry = Inquiry::with('user')->orderBy('id', 'desc');

        if(!Auth::user()->isLoginToManagerService()) {
            $inquiry->where(['user_id' => Auth::id()]);
        }

        // Set Pagination Information
        $pagination = PaginationLibrary::set($request->page, $inquiry->count(), $request->perPage);

        // Get Data from DB
        $data = $inquiry->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // Result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return CollectionLibrary::toCamelCase(collect($result));
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
    public function show(int $id, ShowRequest $request): Collection
    {
        // Get Data from DB
        $data = Inquiry::where('id', $id)
            ->with('answer')
            ->with('attachFiles', function ($q) {
                return $q->select('id', 'url', 'attachable_id', 'attachable_type');
            })->first();

        // Check authority
        if (!$data) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        if (!Auth::user()->isLoginToManagerService()) {
            if ($data->user_id != Auth::id()) {
                throw new QpickHttpException(403, 'inquiry.disable.writer_only');
            }
        }

        // Response
        return CollectionLibrary::toCamelCase(collect($data));
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
    public function update(int $id, UpdateRequest $request)
    {
        // Get Data from DB
        $inquiry = Inquiry::find($id);

        // Check authority
        if (!$inquiry) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        if ($inquiry->user_id != Auth::id()) {
            throw new QpickHttpException(403, 'inquiry.disable.writer_only');
        }

        // Save Data
        $inquiry->title = $request->title ?? $inquiry->title;
        $inquiry->question = $request->question ?? $inquiry->question;
        $inquiry->save();

        // Response
        return response()->json(CollectionLibrary::toCamelCase(collect($inquiry)), 201);
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
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    public function destroy(int $id, DestroyRequest $request)
    {
        // Get Data from DB
        $inquiry = Inquiry::where('id', $id)->first();

        // Check authority
        if (!$inquiry) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        if ($inquiry->user_id != Auth::id()) {
            throw new QpickHttpException(403, 'inquiry.disable.writer_only');
        }

        // Delete
        // TODO - try changing below code to $inquiry->attacheFiles()->delete();
        $this->attachService->delete($inquiry->attachFiles->modelKeys());
        $inquiry->delete();

        // Response
        return response()->noContent();
    }
}
