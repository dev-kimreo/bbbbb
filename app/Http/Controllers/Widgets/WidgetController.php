<?php

namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Widgets\CreateRequest;
use App\Http\Requests\Widgets\IndexRequest;
use App\Http\Requests\Widgets\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Models\Widgets\Widget;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class WidgetController extends Controller
{
    public string $exceptionEntity = "widget";

    /**
     * @OA\Get(
     *      path="/v1/widget",
     *      summary="위젯 목록",
     *      description="어드민 메인화면에서 표시할 위젯의 목록",
     *      operationId="WidgetList",
     *      tags={"위젯"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, description="페이지" ),
     *              @OA\Property(property="perPage", type="integer", example=15, description="한 페이지에 보여질 수" ),
     *              @OA\Property(property="name", type="string", ref="#/components/schemas/Widget/properties/name" ),
     *              @OA\Property(property="enable", type="boolean", example="1", description="사용구분<br/>1:사용, 0:미사용" ),
     *              @OA\Property(property="onlyForManager", type="boolean", example="0", description="관리자 전용 위젯 여부<br/>1:관리자전용, 0:모든 사용자용" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(ref="#/components/schemas/Widget")
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
        $widget = Widget::orderByDesc('id');

        // search condition
        if ($s = $request->input('name')) {
            $widget->where('name', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if (strlen($s = $request->input('enable'))) {
            switch ($s) {
                case 1:
                    $widget->where('enable', '1');
                    break;
                default:
                    $widget->where('enable', '!=', '1');
                    break;
            }
        }

        if (strlen($s = $request->input('only_for_manager'))) {
            switch ($s) {
                case 1:
                    $widget->where('only_for_manager', $s);
                    break;
                default:
                    $widget->where('only_for_manager', '!=', $s);
                    break;
            }
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
     *      path="/v1/widget",
     *      summary="위젯 등록",
     *      description="새로운 위젯을 생성하여 등록",
     *      operationId="widgetCreate",
     *      tags={"위젯"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", ref="#/components/schemas/Widget/properties/name" ),
     *              @OA\Property(property="description", type="string", ref="#/components/schemas/Widget/properties/description" ),
     *              @OA\Property(property="enable", type="boolean", example="1", description="사용구분<br/>1:사용, 0:미사용(기본값)" ),
     *              @OA\Property(property="onlyForManager", type="boolean", example="0", description="관리자 전용 위젯 여부<br/>1:관리자전용, 0:모든 사용자용(기본값)" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Widget")
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
        $widget = Widget::create(array_merge($request->all(), ['user_id' => Auth::id()]));
        return response()->json($this->getOne($widget->id), 201);
    }

    /**
     * @OA\Get(
     *      path="/v1/Widget/{id}",
     *      summary="위젯 상세",
     *      description="위젯 상세정보",
     *      operationId="widgetInfo",
     *      tags={"위젯"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Widget")
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
     *  )
     *
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        return $this->getOne($id);
    }

    /**
     * @OA\Patch(
     *      path="/v1/widget/{id}",
     *      summary="위젯 수정",
     *      description="위젯 정보수정",
     *      operationId="widgetModify",
     *      tags={"위젯"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", ref="#/components/schemas/Widget/properties/name" ),
     *              @OA\Property(property="description", type="string", ref="#/components/schemas/Widget/properties/description" ),
     *              @OA\Property(property="enable", type="boolean", example="1", description="사용구분<br/>1:사용, 0:미사용(기본값)" ),
     *              @OA\Property(property="onlyForManager", type="boolean", example="0", description="관리자 전용 위젯 여부<br/>1:관리자전용, 0:모든 사용자용(기본값)" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Widget")
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
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $widget = Widget::findOrFail($id);
        $widget->update($request->all());

        return response()->json($this->getOne($id), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/widget/{id}",
     *      summary="위젯 삭제",
     *      description="위젯 삭제",
     *      operationId="widgetDelete",
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
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        Widget::findOrFail($id)->delete();
        return response()->noContent();
    }

    protected function getOne(int $id): Collection
    {
        return collect(Widget::findOrFail($id));
    }
}
