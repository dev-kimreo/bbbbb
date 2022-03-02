<?php

namespace App\Http\Controllers\Components;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Components\IndexRequest;
use App\Http\Requests\Components\ShowRequest;
use App\Http\Requests\Components\StoreRequest;
use App\Http\Requests\Components\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Components\Component;
use App\Services\ComponentService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ComponentController extends Controller
{
    public string $exceptionEntity = "component";

    public function __construct()
    {

    }

    /**
     *
     * @OA\Get (
     *      path="/v1/component",
     *      summary="컴포넌트 목록",
     *      description="컴포넌트 목록",
     *      operationId="ComponentIndex",
     *      tags={"컴포넌트"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="solution_id", ref="#/components/schemas/Component/properties/solution_id"),
     *              @OA\Property(property="firstCategory", type="string", example="design", description="검색할 카테고리를 입력<br />theme_component: 테마 컴포넌트<br />product: 상품<br />design: 디자인<br />solution: 솔루션<br />html: HTML"),
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(
     *                  property="sortBy",
     *                  type="string",
     *                  example="+sort,-id",
     *                  description="정렬기준<br/>+:오름차순, -:내림차순"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Component")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "partner_auth":{}
     *      }}
     *  )
     *
     * @param IndexRequest $request
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request): Collection
    {
        $componentBuilder = Component::query();
        //$componentBuilder->where('user_partner_id', Auth::user()->partner->id);

        // Search Parameter
        if ($s = $request->input('first_category')) {
            $componentBuilder->where('first_category', $s);
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($componentBuilder) {
                $componentBuilder->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $componentBuilder->count(), $request->input('per_page'));

        // get data
        return collect($componentBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get());
    }

    /**
     *
     * @OA\Get (
     *      path="/v1/component/{component_id}",
     *      summary="컴포넌트 상세",
     *      description="컴포넌트 상세정보",
     *      operationId="ComponentShow",
     *      tags={"컴포넌트"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Component")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "partner_auth":{}
     *      }}
     *  )
     * @param ShowRequest $request
     * @param int $componentId
     * @return Collection
     * @throws QpickHttpException
     */
    public function show(ShowRequest $request, int $componentId): Collection
    {
        $componentBuilder = Component::query();
        $res = $componentBuilder->findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($res);

        return collect($res);
    }

    /**
     * @OA\Post (
     *      path="/v1/component",
     *      summary="컴포넌트 등록",
     *      description="새로운 컴포넌트를 등록합니다.",
     *      operationId="ComponentCreate",
     *      tags={"컴포넌트"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"solution_id", "name", "first_category", "use_blank", "use_all_page", "display", "status", "icon"},
     *              ref="#/components/schemas/ComponentModifyPossible"
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/EditablePage")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "partner_auth":{}
     *      }}
     *  )
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        // 컴포넌트 유형에 따른 값 설정
        $requestData = $this->setValueAccordingToComponentType($request);

        // 생성 및 response
        return response()->json(
            collect(
                Component::create(array_merge(
                    $requestData,
                    [
                        'user_partner_id' => Auth::user()->partner->id
                    ]
                ))->refresh()
            ), 201
        );
    }

    /**
     *
     * @OA\Patch (
     *      path="/v1/component/{component_id}",
     *      summary="컴포넌트 수정",
     *      description="컴포넌트를 수정합니다.",
     *      operationId="ComponentUpdate",
     *      tags={"컴포넌트"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/ComponentModifyPossible"
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Component")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "partner_auth":{}
     *      }}
     *  )
     *
     * @param UpdateRequest $request
     * @param int $componentId
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function update(UpdateRequest $request, int $componentId): JsonResponse
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        // 컴포넌트 상태가 등록완료시 수정 불가
        if ($component->getAttribute('status') == 'registered') {
            throw new QpickHttpException(422, 'component.disable.modify.registered');
        }

        // 컴포넌트 유형에 따른 값 설정
        $requestData = $this->setValueAccordingToComponentType($request);

        // 수정
        $component->update($requestData);

        return response()->json(collect($component), 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/component/{component_id}",
     *      summary="컴포넌트 삭제",
     *      description="컴포넌트를 삭제합니다",
     *      operationId="ComponentDestroy",
     *      tags={"컴포넌트"},
     *      @OA\Response(
     *          response=204,
     *          description="successfully",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "partner_auth":{}
     *      }}
     *  )
     * @param int $componentId
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $componentId): Response
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        // 컴포넌트 상태가 등록완료시 삭제 불가
        if ($component->getAttribute('status') == 'registered') {
            throw new QpickHttpException(422, 'component.disable.destroy.registered');
        }

        $component->delete();

        return response()->noContent();
    }


    // 컴포넌트 유형에 따른 값 설정
    protected function setValueAccordingToComponentType($request)
    {
        $res = $request->all();

        /**
         * 컴포넌트 유형에 따른 값 설정
         */
        // 본사 컴포넌트 유형일 경우
        if (isset($res['first_category'])) {
            if (in_array($res['first_category'], Component::$onlyQpickCategory)) {
                $res['use_other_than_maker'] = true;
            } else {
                $res['second_category'] = null;
                $res['use_other_than_maker'] = false;
            }
        }

        return $res;
    }

}
