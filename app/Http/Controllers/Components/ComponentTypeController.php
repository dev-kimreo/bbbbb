<?php

namespace App\Http\Controllers\Components;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Components\Types\IndexRequest;
use App\Http\Requests\Components\Types\StoreRequest;
use App\Http\Requests\Components\Types\UpdateRequest;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentType;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ComponentTypeController extends Controller
{
    public string $exceptionEntity = "componentType";

    public function __construct()
    {

    }

    /**
     * @OA\Get (
     *      path="/v1/component-type",
     *      summary="컴포넌트 유형 목록",
     *      description="컴포넌트 유형 목록",
     *      operationId="ComponentTypeIndex",
     *      tags={"컴포넌트 유형"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ComponentType")
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
     */
    public function index(IndexRequest $request): Collection
    {
        return collect(ComponentType::all());
    }

    /**
     * @OA\Get (
     *      path="/v1/component-type/{type_id}",
     *      summary="컴포넌트 유형 상세",
     *      description="컴포넌트 유형 상세정보",
     *      operationId="ComponentTypeShow",
     *      tags={"컴포넌트 유형"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentType")
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
     * @return Collection
     */
    public function show(int $typeId): Collection
    {
        return collect(ComponentType::findOrFail($typeId));
    }

    /**
     * @OA\Post (
     *      path="/v1/component-type",
     *      summary="컴포넌트 유형 등록",
     *      description="새로운 컴포넌트 유형을 등록합니다.",
     *      operationId="ComponentTypeCreate",
     *      tags={"컴포넌트 유형"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", ref="#/components/schemas/ComponentType/properties/name"),
     *              @OA\Property(property="is_plural", ref="#/components/schemas/ComponentType/properties/is_plural"),
     *              @OA\Property(property="has_option", ref="#/components/schemas/ComponentType/properties/has_option"),
     *              @OA\Property(property="has_default", ref="#/components/schemas/ComponentType/properties/has_default"),
     *              @OA\Property(property="max_count", ref="#/components/schemas/ComponentType/properties/max_count"),
     *              @OA\Property(property="attributes", ref="#/components/schemas/ComponentType/properties/attributes"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentType")
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
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        // 생성 및 response
        return response()->json(
            collect(
                ComponentType::create(array_merge(
                    $request->all()
                ))->refresh()
            )
        );
    }

    /**
     * @OA\Patch (
     *      path="/v1/component-type/{type_id}",
     *      summary="컴포넌트 유형 수정",
     *      description="컴포넌트 유형을 수정합니다.",
     *      operationId="ComponentTypeUpdate",
     *      tags={"컴포넌트 유형"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/ComponentType/properties/name"),
     *              @OA\Property(property="is_plural", ref="#/components/schemas/ComponentType/properties/is_plural"),
     *              @OA\Property(property="has_option", ref="#/components/schemas/ComponentType/properties/has_option"),
     *              @OA\Property(property="has_default", ref="#/components/schemas/ComponentType/properties/has_default"),
     *              @OA\Property(property="max_count", ref="#/components/schemas/ComponentType/properties/max_count"),
     *              @OA\Property(property="attributes", ref="#/components/schemas/ComponentType/properties/attributes"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentType")
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
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $typeId): JsonResponse
    {
        $componentType = ComponentType::findOrFail($typeId);
        $componentType->update($request->all());

        return response()->json(collect($componentType), 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/component-type/{type_id}",
     *      summary="컴포넌트 유형 삭제",
     *      description="컴포넌트 유형을 삭제합니다",
     *      operationId="ComponentTypeDestroy",
     *      tags={"컴포넌트 유형"},
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
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $typeId): Response
    {
        $componentType = ComponentType::findOrFail($typeId);

        if (ComponentOption::query()->where('component_type_id', $typeId)->exists()) {
            throw new QpickHttpException(422, 'component_type.disable.destroy.in_use');
        }

        $componentType->delete();

        return response()->noContent();
    }

}
