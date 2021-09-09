<?php

namespace App\Http\Controllers\Widgets;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Widgets\Usages\CreateRequest;
use App\Http\Requests\Widgets\Usages\IndexRequest;
use App\Http\Requests\Widgets\Usages\SortRequest;
use App\Libraries\PaginationLibrary;
use App\Models\Widgets\WidgetUsage;
use Auth;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class WidgetUsageController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/widget/usage",
     *      summary="위젯 사용내역 목록",
     *      description="로그인한 사용자가 사용 중인 위젯의 목록",
     *      operationId="WidgetUsageList",
     *      tags={"위젯"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, description="페이지" ),
     *              @OA\Property(property="perPage", type="integer", example=15, description="한 페이지에 보여질 수" ),
     *              @OA\Property(property="widget_id", type="integer", ref="#/components/schemas/Widget/properties/id" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(ref="#/components/schemas/WidgetUsage")
     *              )
     *          )
     *      )
     *  )
     *
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return Collection
     */
    public function index(IndexRequest $request): Collection
    {
        // init model
        $widget = WidgetUsage::orderBy('sort');
        $widget->where('user_id', Auth::id());

        // search condition
        if ($s = $request->input('widget_id')) {
            $widget->where('widget_id', $s);
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $widget->count(), $request->input('per_page'));

        // get data from DB
        $data = $widget->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }

    /**
     * @OA\Post(
     *      path="/v1/widget/usage",
     *      summary="위젯 사용내역 등록",
     *      description="로그인 된 아이디에 새로운 위젯을 사용하게 함",
     *      operationId="widgetUsageCreate",
     *      tags={"위젯"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"widget_id"},
     *              @OA\Property(property="widget_id", type="integer", ref="#/components/schemas/Widget/properties/id" )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/WidgetUsage")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Store a newly created resource in storage.
     *
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function store(CreateRequest $request): JsonResponse
    {
        $widget = WidgetUsage::create([
            'widget_id' => $request->input('widget_id'),
            'user_id' => Auth::id(),
            'sort' => WidgetUsage::where('user_id', Auth::id())->max('sort') + 1
        ]);

        return response()->json($this->getOne($widget->id), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        return $this->getOne($id);
    }

    /**
     * @OA\delete(
     *      path="/v1/widget/usage/{id}",
     *      summary="위젯 사용내역 삭제",
     *      description="로그인한 사용자가 해당 위젯을 더이상 사용하지 않도록 사용내역을 삭제",
     *      operationId="widgetUsageDelete",
     *      tags={"위젯"},
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
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $id): Response
    {
        $usage = WidgetUsage::findOrFail($id);

        // Check authority
        if ($usage->user_id != Auth::id()) {
            throw new QpickHttpException(403, 'widget_usage.disable.writer_only');
        }

        // Delete
        $usage->delete();

        // Response
        return response()->noContent();
    }

    /**
     * @OA\Patch(
     *      path="/v1/widget/{id}/sort",
     *      summary="위젯 사용내역 순서변경",
     *      description="로그인된 사용자가 사용하는 위젯의 배치순서를 변경합니다",
     *      operationId="widgetUsageSort",
     *      tags={"위젯"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="target", type="integer", example="235", description="옮겨갈 위치의 위젯 사용내역 id" ),
     *              @OA\Property(property="direction", type="string", example="bottom", description="정렬할 지점<br/>top: target의 위<br/>bottom: target의 아래" )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/WidgetUsage")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * @param SortRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function sort(SortRequest $request, int $id): JsonResponse
    {
        $obj = WidgetUsage::findOrFail($id);
        $tar = WidgetUsage::findOrFail($request->input('target'));

        if ($obj->sort < $tar->sort) {
            $start = $obj->sort;
            $end = $tar->sort - ($request->input('direction') == 'top' ? 1 : 0);
            $trx = 'sort - 1';
            $fin = $end;
        } else
        {
            $start = $tar->sort + ($request->input('direction') == 'bottom' ? 1 : 0);
            $end = $obj->sort;
            $trx = 'sort + 1';
            $fin = $start;
        }

        if($start != $end) {
            WidgetUsage::where('user_id', Auth::id())
                ->whereBetween('sort', [$start, $end])
                ->update(['sort' => DB::raw($trx)]);
            $obj->update(['sort' => $fin]);
        }

        return response()->json($this->getOne($id), 201);
    }

    protected function getOne(int $id): Collection
    {
        return collect(WidgetUsage::findOrFail($id));
    }
}
