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


    public function store(CreateRequest $request)
    {
        // Bacckoffice login
        if (auth()->user()->isLoginToManagerService()) {

        } else {
            // check write Policy
            if (!auth()->user()->can('create', [$this->inquiry])) {
                throw new QpickHttpException(403, 101001);
            }

            // 데이터 가공
            $this->inquiry->user_id = auth()->user()->id;
            $this->inquiry->title = $request->title;
            $this->inquiry->question = $request->question;
            $this->inquiry->created_at = Carbon::now();
            $this->inquiry->save();

            return response()->json($this->inquiry, 201);
        }
    }

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
            // where 절
            $whereModel = $this->inquiry->where(['user_id' => auth()->user()->id]);

            // pagination
            $pagination = PaginationLibrary::set($set['page'], $whereModel->count(), $set['view']);

            // 문의 정보
            $inquiry =
                $whereModel
                    ->skip($pagination['skip'])
                    ->take($pagination['perPage'])
                    ->orderBy('id', 'desc');

            $data = $inquiry->get();
        }

        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return response()->json(CollectionLibrary::toCamelCase(collect($result)), 200);
    }

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
                throw new QpickHttpException(422, 100005);
            }
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($collect)), 200);
    }

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
                throw new QpickHttpException(422, 100005);
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
                throw new QpickHttpException(422, 100005);
            }

            // 첨부파일 삭제
            $this->attachService->delete($collect->attachFiles->modelKeys());

            // 문의 소프트 삭제
            $collect->delete();

            return response()->noContent();
        }
    }


}
