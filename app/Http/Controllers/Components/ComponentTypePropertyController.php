<?php

namespace App\Http\Controllers\Components;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Components\Types\Properties\IndexRequest;
use App\Http\Requests\Components\Types\Properties\StoreRequest;
use App\Http\Requests\Components\Types\Properties\UpdateRequest;
use App\Models\Components\ComponentTypeProperty;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ComponentTypePropertyController extends Controller
{
    public string $exceptionEntity = "componentTypeProperty";

    public function __construct()
    {

    }

    /**
     * @OA\Get (
     *      path="/v1/component-type/{type_id}/property",
     *      summary="컴포넌트 옵션 유형 목록",
     *      description="컴포넌트 옵션 유형 목록",
     *      operationId="ComponentTypePropertyIndex",
     *      tags={"컴포넌트 옵션 유형"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ComponentTypeProperty")
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
     * @param int $typeId
     * @return Collection
     */
    public function index(IndexRequest $request, int $typeId): Collection
    {
        $componentTypePptBuild = ComponentTypeProperty::query();
        $componentTypePptBuild->where('component_type_id', $typeId);
        $componentTypePptBuild->orderBy('id', 'asc');
        $res = $componentTypePptBuild->get();

        // get data
        return collect($res);
    }

    /**
     * @OA\Get (
     *      path="/v1/component-type/{type_id}/property/{property_id}",
     *      summary="컴포넌트 옵션 유형 상세",
     *      description="컴포넌트 옵션 유형 상세정보",
     *      operationId="ComponentTypePropertyShow",
     *      tags={"컴포넌트 옵션 유형"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentTypeProperty")
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
     * @param int $typeId
     * @param int $propertyId
     * @return Collection
     */
    public function show(int $typeId, int $propertyId): Collection
    {
        return collect(ComponentTypeProperty::where('component_type_id', $typeId)->findOrFail($propertyId));
    }


    /**
     * @OA\Post (
     *      path="/v1/component-type/{type_id}/property",
     *      summary="컴포넌트 옵션 유형 등록",
     *      description="새로운 컴포넌트 옵션 유형을 등록합니다.",
     *      operationId="ComponentTypePropertyCreate",
     *      tags={"컴포넌트 옵션 유형"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"type"},
     *              @OA\Property(property="type", ref="#/components/schemas/ComponentTypeProperty/properties/type"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentTypeProperty")
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
     * @param StoreRequest $request
     * @param int $typeId
     * @return JsonResponse
     */
    public function store(StoreRequest $request, int $typeId): JsonResponse
    {
        // 생성 및 response
        return response()->json(
            collect(
                ComponentTypeProperty::create(array_merge(
                    $request->all(),
                    [
                        'component_type_id' => $typeId,
                    ]
                ))->refresh()
            )
        );
    }

    /**
     * @OA\Patch (
     *      path="/v1/component-type/{type_id}/property/{property_id}",
     *      summary="컴포넌트 옵션 유형 수정",
     *      description="컴포넌트 옵션 유형을 수정합니다.",
     *      operationId="ComponentTypePropertyUpdate",
     *      tags={"컴포넌트 옵션 유형"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="type", ref="#/components/schemas/ComponentTypeProperty/properties/type"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentTypeProperty")
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
     * @param int $typeId
     * @param int $propertyId
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $typeId, int $propertyId): JsonResponse
    {
        // 수정
        $componentTypePptData = ComponentTypeProperty::findOrFail($propertyId);
        $componentTypePptData->update($request->all());

        return response()->json(collect($componentTypePptData), 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/component-type/{type_id}/property/{property_id}",
     *      summary="컴포넌트 옵션 유형 삭제",
     *      description="컴포넌트 옵션 유형을 삭제합니다",
     *      operationId="ComponentTypePropertyDestroy",
     *      tags={"컴포넌트 옵션 유형"},
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
     *
     * @param int $typeId
     * @param int $propertyId
     * @return Response
     */
    public function destroy(int $typeId, int $propertyId): Response
    {
        $componentTypePptData = ComponentTypeProperty::findOrFail($propertyId);
        $componentTypePptData->delete();

        return response()->noContent();
    }


}
