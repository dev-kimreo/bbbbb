<?php


namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\Inquiries\CreateRequest;
use App\Http\Requests\Inquiries\DestroyRequest;
use App\Http\Requests\Inquiries\IndexRequest;
use App\Http\Requests\Inquiries\ShowRequest;
use App\Http\Requests\Inquiries\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Models\Inquiry;
use App\Models\InquiryAnswer;
use App\Models\User;
use App\Services\AttachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     *      @OA\Property(property="title", type="string", example="1:1 문의 제목입니다.", description="1:1 문의 제목"),
     *      @OA\Property(property="question", type="string", example="1:1 문의 내용입니다.", description="1:1 문의 내용"),
     *      @OA\Property(property="assignee_id", type="integer", example="5", description="1:1 문의 처리담당자")
     *  )
     *
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
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/Inquiry"),
     *                  @OA\Schema(
     *                      @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="answer", type="null")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="assignee", type="null")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="attachFiles", type="array",
     *                          @OA\Items(ref="#/components/schemas/AttachFile")
     *                      )
     *                  )
     *              }
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
    public function store(CreateRequest $request): JsonResponse
    {
        // 초기화
        $inquiry = $this->inquiry;
        $inquiry->timestamps = false;

        // 데이터 가공
        $inquiry->setAttribute('user_id', Auth::id());
        $inquiry->setAttribute('title', $request->input('title'));
        $inquiry->setAttribute('question', $request->input('question'));
        $inquiry->setAttribute('assignee_id', $request->input('assignee_id'));
        $inquiry->setAttribute('created_at', Carbon::now());
        $inquiry->save();

        // Response
        $data = $this->getOne($inquiry->id);
        return response()->json(CollectionLibrary::toCamelCase(collect($data)), 201);
    }


    /**
     * @OA\Schema (
     *      schema="inquiryList",
     *      @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *      @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *      @OA\Property(property="id", type="string", example=1, description="1:1문의의 고유번호(PK)"),
     *      @OA\Property(property="status", type="string", example=1, description="상태값"),
     *      @OA\Property(property="startDate", type="date(Y-m-d)", example=1, description="접수기간 검색 시작일"),
     *      @OA\Property(property="endDate", type="date(Y-m-d)", example=1, description="접수기간 검색 종료일"),
     *      @OA\Property(property="title", type="string", example=1, description="제목 검색어"),
     *      @OA\Property(property="userId", type="integer", example=1, description="작성한 사용자의 고유번호(PK)"),
     *      @OA\Property(property="userEmail", type="string", example=1, description="작성한 사용자의 이메일"),
     *      @OA\Property(property="userName", type="string", example=1, description="작성한 사용자의 이름"),
     *      @OA\Property(property="assigneeId", type="integer", example=1, description="처리담당자의 고유번호(PK)"),
     *      @OA\Property(property="assigneeName", type="string", example=1, description="처리담당자의 이름"),
     *      @OA\Property(property="multiSearch", type="string|integer", example=1, description="통합검색을 위한 검색어")
     * )
     *
     * @OA\Schema (
     *      schema="inquiryListElement",
     *      allOf={
     *          @OA\Schema (
     *              @OA\Property(property="id", type="integer", example=1, description="고유 번호" ),
     *              @OA\Property(property="title", type="string", example="1:1 문의 제목", description="1:1문의 제목" ),
     *              @OA\Property(property="question", type="string", example="1:1 문의 내용", description="1:1문의 내용" ),
     *              @OA\Property(property="status", type="string", example="waiting", description="처리상태<br/>waiting:접수<br/>answering:확인중<br/>answered:완료" ),
     *              @OA\Property(property="createdAt", type="ISO 8601 date", example="2021-02-12T15:19:21+00:00", description="등록일자"),
     *              @OA\Property(property="updatedAt", type="ISO 8601 date", example="2021-02-13T18:52:16+00:00", description="수정일자"),
     *              @OA\Property(property="answered", type="boolean", example="true", description="답변완료 여부")
     *          ),
     *          @OA\Schema (
     *              @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply")
     *          ),
     *          @OA\Schema (
     *              @OA\Property(property="referrer", type="object", ref="#/components/schemas/UserSimply")
     *          ),
     *          @OA\Schema (
     *              @OA\Property(property="assignee", type="object", ref="#/components/schemas/UserSimply")
     *          )
     *      }
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
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/inquiryListElement")
     *              )
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

        // Set a query builder
        $inquiry = DB::table('inquiries')
            ->select('inquiries.*')
            ->orderBy('inquiries.id', 'desc');

        // Set search conditions
        if (!Auth::user()->isLoginToManagerService()) {
            $inquiry->where('inquiries.user_id', Auth::id());
        }

        if ($s = $request->get('id')) {
            $inquiry->where('inquiries.id', $s);
        }

        if ($s = $request->get('status')) {
            $inquiry->where('inquiries.status', $s);
        }

        if ($s = $request->get('startDate')) {
            $s = Carbon::parse($s);
            $inquiry->where('inquiries.created_at', '>=', $s);
        }

        if ($s = $request->get('endDate')) {
            $s = Carbon::parse($s)->setTime(23, 59, 59);
            $inquiry->where('inquiries.created_at', '<=', $s);
        }

        if ($s = $request->get('multiSearch')) {
            // 통합검색
            $inquiry->join('users as users_ms', 'inquiries.user_id', '=', 'users_ms.id');
            $inquiry->leftJoin('users as assignees_ms', 'inquiries.assignee_id', '=', 'assignees_ms.id');

            $inquiry->where(function ($q) use ($s) {
                $q->orWhere('inquiries.title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
                $q->orWhere('users_ms.email', 'like', '"%' . StringLibrary::escapeSql($s) . '%');
                $q->orWhere('users_ms.name', $s);
                $q->orWhere('assignees_ms.name', $s);

                if (is_numeric($s)) {
                    $q->orWhere('inquiries.id', $s);
                }
            });
        }

        if ($s = $request->get('title')) {
            $inquiry->where('inquiries.title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->get('userId')) {
            $inquiry->where('inquiries.user_id', $s);
        }

        if ($request->get('userEmail') || $request->get('userName')) {
            $inquiry->join('users', 'inquiries.user_id', '=', 'users.id');

            if ($s = $request->get('userEmail')) {
                $inquiry->where('users.email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
            }

            if ($s = $request->get('userName')) {
                $inquiry->where('users.name', $s);
            }
        }

        if ($s = $request->get('assigneeId')) {
            $inquiry->where('inquiries.assignee_id', $s);
        }

        if ($s = $request->get('assigneeName')) {
            $inquiry->join('users as assignees', 'inquiries.assignee_id', '=', 'assignees.id');
            $inquiry->where('assignees.name', $s);
        }

        // Set Pagination Information
        $pagination = PaginationLibrary::set($request->page, $inquiry->count(), $request->perPage);

        // Get Data from DB
        $data = $inquiry->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // Post processing
        $data->each(function ($item) {
            // Edit data
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at)->toIso8601String() : null;
            $item->updated_at = $item->updated_at ? Carbon::parse($item->updated_at)->toIso8601String() : null;
            unset($item->deleted_at);

            // Check if there is a related data
            $item->answered = InquiryAnswer::where('inquiry_id', $item->id)->exists();

            // Getting data from related table
            $item->user = $this->getUser($item->user_id);
            $item->referrer = $this->getUser($item->referrer_id);
            $item->assignee = is_null($item->assignee_id) ? null : $this->getUser($item->assignee_id);
            unset($item->deleted_at, $item->user_id, $item->referrer_id, $item->assignee_id);
        });

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
     *                  @OA\Schema(ref="#/components/schemas/Inquiry"),
     *                  @OA\Schema(
     *                      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="referrer", type="object", ref="#/components/schemas/UserSimply")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="assignee", type="object", ref="#/components/schemas/UserSimply")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="answer", type="object", ref="#/components/schemas/InquiryAnswer")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="attachFiles", type="array",
     *                          @OA\Items(ref="#/components/schemas/AttachFile")
     *                      )
     *                  )
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
        $data = $this->getOne($id);

        // Check authority
        if (!Auth::user()->isLoginToManagerService()) {
            if ($data->user_id != Auth::id()) {
                throw new QpickHttpException(403, 'inquiry.disable.writer_only');
            }
        }

        // Response
        return CollectionLibrary::toCamelCase(collect($data));
    }


    /**
     * @OA\Schema (
     *      schema="inquiryModify",
     *      required={},
     *      @OA\Property(property="title", type="string", example="1:1 문의 제목입니다.", description="1:1 문의 제목"),
     *      @OA\Property(property="question", type="string", example="1:1 문의 내용입니다.", description="1:1 문의 내용"),
     *      @OA\Property(property="assignee_id", type="integer", example="5", description="1:1 문의 처리담당자")
     *  )
     *
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
     *              ref="#/components/schemas/inquiryModify"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/Inquiry"),
     *                  @OA\Schema(
     *                      @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="answer", type="object", ref="#/components/schemas/InquiryAnswer")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="assignee", type="object", ref="#/components/schemas/User")
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="attachFiles", type="array",
     *                          @OA\Items(ref="#/components/schemas/AttachFile")
     *                      )
     *                  )
     *              }
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
        $inquiry = Inquiry::findOrFail($id);

        // Check authority
        if ($inquiry->user_id != Auth::id()) {
            throw new QpickHttpException(403, 'inquiry.disable.writer_only');
        }

        // Save Data
        $inquiry->title = $request->title ?? $inquiry->title;
        $inquiry->question = $request->question ?? $inquiry->question;
        $inquiry->assignee_id = $request->assigneeId ?? $inquiry->assignee_id;
        $inquiry->referrer_id = $request->referrerId ?? $inquiry->referrer_id;
        $inquiry->save();

        // Response
        $data = $this->getOne($id);
        return response()->json(CollectionLibrary::toCamelCase(collect($data)), 201);
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

    /* Custom Methods */
    protected function getUser($id)
    {
        static $users = [];

        return $users[$id] ?? ($users[$id] = User::select(['id', 'name', 'email'])->find($id));
    }

    protected function getOne(int $id)
    {
        return Inquiry::with(['user', 'referrer', 'assignee', 'answer', 'attachFiles'])->findOrFail($id);
    }
}
