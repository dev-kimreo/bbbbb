<?php


namespace App\Http\Controllers;

use App\Events\Backoffice\DataUpdated;
use App\Exceptions\QpickHttpException;
use App\Http\Requests\Inquiries\AssignRequest;
use App\Http\Requests\Inquiries\CreateRequest;
use App\Http\Requests\Inquiries\DestroyRequest;
use App\Http\Requests\Inquiries\IndexRequest;
use App\Http\Requests\Inquiries\ShowRequest;
use App\Http\Requests\Inquiries\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Models\Inquiry;
use App\Models\InquiryAnswer;
use App\Models\Users\User;
use App\Services\AttachService;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
     *      @OA\Property(property="assigneeId", type="integer", example="5", description="1:1 문의 처리담당자")
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
     *          @OA\JsonContent(ref="#/components/schemas/Inquiry")
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
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function store(CreateRequest $request): JsonResponse
    {
        // 초기화
        $inquiry = $this->inquiry;

        // 데이터 가공
        $inquiry->user_id = Auth::id();
        $inquiry->title = $request->input('title');
        $inquiry->question = $request->input('question');
        $inquiry->assignee_id = $request->input('assignee_id');
        $inquiry->save();

        // Response
        $data = $this->getOne($inquiry->id);
        return response()->json(collect($data), 201);
    }


    /**
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
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="id", type="string", example=1, description="1:1문의의 고유번호(PK)"),
     *              @OA\Property(property="status[]", type="string", example="waiting", description="검색할 상태값(다중입력 가능)<br>waiting:접수, answering:확인중, answered:완료"),
     *              @OA\Property(property="startDate", type="date(Y-m-d)", example=1, description="접수기간 검색 시작일"),
     *              @OA\Property(property="endDate", type="date(Y-m-d)", example=1, description="접수기간 검색 종료일"),
     *              @OA\Property(property="title", type="string", example=1, description="제목 검색어"),
     *              @OA\Property(property="userId", type="integer", example=1, description="문의를 작성한 사용자의 고유번호(PK)"),
     *              @OA\Property(property="userEmail", type="string", example=1, description="문의를 작성한 사용자의 이메일"),
     *              @OA\Property(property="userName", type="string", example=1, description="문의를 작성한 사용자의 이름"),
     *              @OA\Property(property="assigneeId", type="integer", example=1, description="처리담당자의 고유번호(PK)"),
     *              @OA\Property(property="assigneeName", type="string", example=1, description="처리담당자의 이름"),
     *              @OA\Property(property="answerId", type="integer", example=1, description="답변한 관리자의 고유번호(PK)"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/InquiryForList")
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
     * @param IndexRequest $request
     * @return Collection
     */
    public function index(IndexRequest $request): Collection
    {
        return $this->indexByEloquentOrm($request);
        //return $this->indexByQueryBuilder($request);
    }

    protected function indexByEloquentOrm(IndexRequest $request): Collection
    {
        // init model
        $inquiry = Inquiry::with('user', 'referrer', 'assignee')
            ->orderByDesc('id');

        // set search conditions
        if (!Auth::hasAccessRightsToBackoffice()) {
            $inquiry->where('user_id', Auth::id());
        }

        if ($s = $request->input('id')) {
            $inquiry->where('id', $s);
        }

        if (is_array($s = $request->input('status'))) {
            $inquiry->whereIn('status', $s);
        }

        if ($s = $request->input('start_date')) {
            $s = Carbon::parse($s);
            $inquiry->where('created_at', '>=', $s);
        }

        if ($s = $request->input('end_date')) {
            $s = Carbon::parse($s)->setTime(23, 59, 59);
            $inquiry->where('created_at', '<=', $s);
        }

        if ($s = $request->input('title')) {
            $inquiry->where('title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->input('user_id')) {
            $inquiry->where('user_id', $s);
        }

        if ($s = $request->input('user_email')) {
            $inquiry->whereHas('user', function (Builder $q) use ($s) {
                $q->where('email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
            });
        }

        if ($s = $request->input('user_name')) {
            $inquiry->whereHas('user', function (Builder $q) use ($s) {
                $q->where('name', $s);
            });
        }

        if ($s = $request->input('assignee_id')) {
            $inquiry->where('assignee_id', $s);
        }

        if ($s = $request->input('assignee_name')) {
            $inquiry->whereHas('assignee', function (Builder $q) use ($s) {
                $q->where('name', $s);
            });
        }

        if ($s = $request->input('answer_id')) {
            $inquiry->whereHas('answer', function (Builder $q) use ($s) {
                $q->where('user_id', $s);
            });
        }

        // Set Pagination Information
        $pagination = PaginationLibrary::set($request->input('page'), $inquiry->count(), $request->input('per_page'));

        // Get Data from DB
        $data = $inquiry->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // Post processing
        $data->each(function ($item) {
            // Check if there is a related data
            $answer = InquiryAnswer::where('inquiry_id', $item->id);
            $item->answered = $answer->exists();
            $item->answered_at = ($item->answered) ? $answer->first()->created_at : null;

            // Getting attach
            $item->attached = Inquiry::find($item->id)->attachFiles()->exists();
        });

        // Result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }

    protected function indexByQueryBuilder(IndexRequest $request): Collection
    {
        // check viewAny Policy
//        if (!auth()->user()->can('viewAny', [$this->inquiry])) {
//            throw new QpickHttpException(403, 'common.unauthorized');
//        }

        // Set a query builder
        $inquiry = DB::table('inquiries')
            ->select('inquiries.*')
            ->whereNull('deleted_at')
            ->orderBy('inquiries.id', 'desc');

        // Set search conditions
        if (!Auth::hasAccessRightsToBackoffice()) {
            $inquiry->where('inquiries.user_id', Auth::id());
        }

        if ($s = $request->input('id')) {
            $inquiry->where('inquiries.id', $s);
        }

        if (is_array($s = $request->input('status'))) {
            $inquiry->whereIn('inquiries.status', $s);
        }

        if ($s = $request->input('start_date')) {
            $s = Carbon::parse($s);
            $inquiry->where('inquiries.created_at', '>=', $s);
        }

        if ($s = $request->input('end_date')) {
            $s = Carbon::parse($s)->setTime(23, 59, 59);
            $inquiry->where('inquiries.created_at', '<=', $s);
        }

        /*
        if ($s = $request->input('multi_search')) {
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
        */

        if ($s = $request->input('title')) {
            $inquiry->where('inquiries.title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->input('user_id')) {
            $inquiry->where('inquiries.user_id', $s);
        }

        if ($request->input('user_email') || $request->input('user_name')) {
            $inquiry->join('users', 'inquiries.user_id', '=', 'users.id');

            if ($s = $request->input('user_email')) {
                $inquiry->where('users.email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
            }

            if ($s = $request->input('user_name')) {
                $inquiry->where('users.name', $s);
            }
        }

        if ($s = $request->input('assignee_id')) {
            $inquiry->where('inquiries.assignee_id', $s);
        }

        if ($s = $request->input('assignee_name')) {
            $inquiry->join('users as assignees', 'inquiries.assignee_id', '=', 'assignees.id');
            $inquiry->where('assignees.name', $s);
        }

        // Set Pagination Information
        $pagination = PaginationLibrary::set($request->input('page'), $inquiry->count(), $request->input('per_page'));

        // Get Data from DB
        $data = $inquiry->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // Post processing
        $data->each(function ($item) {
            // Edit data
            $item->assigned_at = $item->assigned_at ? Carbon::parse($item->assigned_at)->toIso8601String() : null;
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at)->toIso8601String() : null;
            $item->updated_at = $item->updated_at ? Carbon::parse($item->updated_at)->toIso8601String() : null;
            unset($item->deleted_at);

            if ($item->updated_at == $item->created_at) {
                $item->updated_at = null;
            }

            // Check if there is a related data
            $answer = InquiryAnswer::where('inquiry_id', $item->id);
            $item->answered = $answer->exists();
            $item->answered_at = ($item->answered) ? $answer->first()->created_at : null;

            // Getting attach
            $item->attached = Inquiry::find($item->id)->attachFiles()->exists();

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

        return collect($result);
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
     *          @OA\JsonContent(ref="#/components/schemas/Inquiry")
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
     * @param int $id
     * @param ShowRequest $request
     * @return Collection
     * @throws QpickHttpException
     */
    public function show(ShowRequest $request, int $id): Collection
    {
        // Get Data from DB
        $data = $this->getOne($id);

        // Check authority
        if (!Auth::hasAccessRightsToBackoffice()) {
            if ($data->user_id != Auth::id()) {
                throw new QpickHttpException(403, 'inquiry.disable.writer_only');
            }
        }

        // Response
        return collect($data);
    }


    /**
     * @OA\Schema (
     *      schema="inquiryModify",
     *      required={},
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
     *              @OA\Property(property="title", type="string", example="1:1 문의 제목입니다.", description="1:1 문의 제목"),
     *              @OA\Property(property="question", type="string", example="1:1 문의 내용입니다.", description="1:1 문의 내용"),
     *              @OA\Property(property="referrerId", type="integer", example="5", description="문의계정의 사용자 고유번호")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(ref="#/components/schemas/Inquiry")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
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
     *
     * @param int $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        // Get Data from DB
        $inquiry = Inquiry::findOrFail($id);

        // Check authority
        if ($inquiry->user_id != Auth::id() && !Auth::hasAccessRightsToBackoffice()) {
            throw new QpickHttpException(403, 'inquiry.disable.writer_only');
        }

        // Save Data
        $inquiry->title = $request->input('title', $inquiry->title);
        $inquiry->question = $request->input('question', $inquiry->question);
        $inquiry->referrer_id = $request->input('referrer_id', $inquiry->referrer_id);
        $inquiry->save();

        // Response
        $data = $this->getOne($id);
        return response()->json(collect($data), 201);
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
     *          response=401,
     *          description="Unauthenticated (비로그인)"
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
     * @param int $id
     * @param DestroyRequest $request
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(DestroyRequest $request, int $id): Response
    {
        // Get Data from DB
        $inquiry = Inquiry::findOrFail($id);

        // Check authority
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

    /**
     * @OA\Patch(
     *      path="/v1/inquiry/{inquiry_id}/assignee/{assignee_id}",
     *      summary="1:1문의 접수(처리담당자 변경)",
     *      description="1:1문의 접수(처리담당자 변경)",
     *      operationId="inquiryAssign",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(ref="#/components/schemas/Inquiry")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found (답변할 1:1 문의가 존재하지 않음)"
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Conflict (이미 답변이 완료되어 접수 및 담당자 변경이 불가능함)"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param AssignRequest $request
     * @param int $inquiry_id
     * @param int $assignee_id
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function assignee(AssignRequest $request, int $inquiry_id, int $assignee_id): JsonResponse
    {
        // Get Data from DB
        $inquiry = Inquiry::findOrFail($inquiry_id);

        // Check authority
        if ($inquiry->answer) {
            throw new QpickHttpException(409, 'inquiry.answer.disable.already_exists');
        }

        // Save Data
        if ($inquiry->status == Inquiry::$status['waiting']) {
            $inquiry->status = Inquiry::$status['answering'];
        }
        $inquiry->assignee_id = $assignee_id;
        $inquiry->assigned_at = Carbon::now();
        $inquiry->save();

        // Send an event for remaining backoffice logs
        DataUpdated::dispatch($inquiry, $inquiry_id, '접수');

        // Response
        $data = $this->getOne($inquiry_id);
        return response()->json(collect($data), 201);
    }


    /**
     * @OA\Get(
     *      path="/v1/statistics/inquiry/count-per-status",
     *      summary="1:1문의 상태별 통계",
     *      description="1:1문의 상태별 통계",
     *      operationId="inquiryGetCountPerStatus",
     *      tags={"통계"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="status", type="string", example="waiting", description="상태"),
     *                  @OA\Property(property="count", type="integer", example=30, description="갯수"),
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
     * @return Collection
     */
    public function getCountPerStatus(): Collection
    {
        $res = Cache::tags('backoffice')->remember('inquiry_count_per_status', config('cache.custom.expire.common'), function () {
            return $this->inquiry->selectRaw('status, count(id) as count')
                ->whereIn('status', [Inquiry::$status['waiting'], Inquiry::$status['answering']])
                ->groupBy('status')
                ->get();
        });

        foreach (Inquiry::$status as $v) {
            if (!$res->contains('status', $v)) {
                $res->push([
                    'status' => $v,
                    'count' => 0
                ]);
            }
        }

        return $res;
    }

    /* Custom Methods */
    protected function getUser($id)
    {
        static $users = [];

        return $users[$id] ?? ($users[$id] = User::select(['id', 'name', 'email'])->find($id));
    }

    protected function getOne(int $id)
    {
        $with = ['user', 'referrer', 'assignee', 'answer', 'attachFiles'];

        if (Auth::hasAccessRightsToBackoffice()) {
            $with[] = 'backofficeLogs';
        }

        return Inquiry::with($with)->findOrFail($id);
    }
}
