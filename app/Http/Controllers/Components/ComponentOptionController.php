<?php

namespace App\Http\Controllers\Components;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Components\Options\IndexRequest;
use App\Http\Requests\Components\Options\ShowRequest;
use App\Http\Requests\Components\Options\StoreRequest;
use App\Http\Requests\Components\Options\UpdateRequest;
use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentOptionProperty;
use App\Models\Components\ComponentType;
use App\Models\Components\ComponentVersion;
use App\Services\ComponentService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ComponentOptionController extends Controller
{
    public string $exceptionEntity = "componentOption";

    public function __construct()
    {

    }

    /**
     * @OA\Get (
     *      path="/v1/component/{component_id}/version/{version_id}/option",
     *      summary="컴포넌트 옵션 목록",
     *      description="컴포넌트 옵션 목록",
     *      operationId="ComponentOptionIndex",
     *      tags={"컴포넌트 옵션"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ComponentOption")
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
     * @param int $componentId
     * @param int $versionId
     * @return Collection
     */
    public function index(IndexRequest $request, int $componentId, int $versionId): Collection
    {
        $builder = ComponentOption::query();
        $builder->where('component_version_id', $versionId);
        $builder->orderBy('id', 'asc');
        $res = $builder->get();

        // get data
        return collect($res);
    }

    /**
     * @OA\Get (
     *      path="/v1/component/{component_id}/version/{version_id}/option/{option_id}",
     *      summary="컴포넌트 옵션 상세 (상위모델 참고)",
     *      description="컴포넌트 옵션 상세정보",
     *      operationId="ComponentOptionShow",
     *      tags={"컴포넌트 옵션"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="with_option_properties", type="boolean", example=1, description="컴포넌트 옵션 속성 데이터 포함 여부" ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentOption")
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
     * @OA\Get (
     *      path="/v1/component-option/{option_id}",
     *      summary="컴포넌트 옵션 상세",
     *      description="컴포넌트 옵션 상세정보",
     *      operationId="ComponentOptionShow",
     *      tags={"컴포넌트 옵션"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="with_option_properties", type="boolean", example=1, description="컴포넌트 옵션 속성 데이터 포함 여부" ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentOption")
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
     * @param ShowRequest $request
     * @return Collection
     */
    public function show(ShowRequest $request): Collection
    {
        $res = ComponentOption::query()->findOrFail($request->route('option_id'));

        if ($request->input('with_option_properties')) {
            $res->getAttribute('properties');
        }

        return collect($res);
    }

    /**
     * @OA\Post (
     *      path="/v1/component/{component_id}/version/{version_id}/option",
     *      summary="컴포넌트 옵션 등록",
     *      description="새로운 컴포넌트 옵션을 등록합니다.",
     *      operationId="ComponentOptionCreate",
     *      tags={"컴포넌트 옵션"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"component_type_id", "name", "key"},
     *              @OA\Property(property="component_type_id", ref="#/components/schemas/ComponentOption/properties/component_type_id"),
     *              @OA\Property(property="name", ref="#/components/schemas/ComponentOption/properties/name"),
     *              @OA\Property(property="key", ref="#/components/schemas/ComponentOption/properties/key"),
     *              @OA\Property(property="display_on_pc", ref="#/components/schemas/ComponentOption/properties/display_on_pc"),
     *              @OA\Property(property="display_on_mobile", ref="#/components/schemas/ComponentOption/properties/display_on_mobile"),
     *              @OA\Property(property="hideable", ref="#/components/schemas/ComponentOption/properties/hideable"),
     *              @OA\Property(property="attributes", ref="#/components/schemas/ComponentOption/properties/attributes"),
     *              @OA\Property(property="help", ref="#/components/schemas/ComponentOption/properties/help"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentOption")
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
     * @param int $componentId
     * @param int $versionId
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, int $componentId, int $versionId): JsonResponse
    {
        ComponentVersion::findOrFail($versionId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant(Component::findOrFail($componentId));

        // 컴포넌트 유형 확인
        $componentType = ComponentType::findOrFail($request->input('component_type_id'));

        // TODO Component type 의 Attribute 의 데이터 로직이 추가 되야함

        // 생성 및 response
        $data = array_merge(
            $request->all(),
            [
                'component_version_id' => $versionId,
                'component_type_id' => $componentType->getAttribute('id'),
            ]
        );

        return response()->json($this->createOption($data));
    }

    /**
     * @OA\Patch (
     *      path="/v1/component/{component_id}/version/{version_id}/option/{option_id}",
     *      summary="컴포넌트 옵션 수정",
     *      description="컴포넌트 옵션을 수정합니다.",
     *      operationId="ComponentOpionUpdate",
     *      tags={"컴포넌트 옵션"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/ComponentOption/properties/name"),
     *              @OA\Property(property="key", ref="#/components/schemas/ComponentOption/properties/key"),
     *              @OA\Property(property="display_on_pc", ref="#/components/schemas/ComponentOption/properties/display_on_pc"),
     *              @OA\Property(property="display_on_mobile", ref="#/components/schemas/ComponentOption/properties/display_on_mobile"),
     *              @OA\Property(property="hideable", ref="#/components/schemas/ComponentOption/properties/hideable"),
     *              @OA\Property(property="attributes", ref="#/components/schemas/ComponentOption/properties/attributes"),
     *              @OA\Property(property="help", ref="#/components/schemas/ComponentOption/properties/help"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentOption")
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
     * @param int $versionId
     * @param int $optionId
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function update(UpdateRequest $request, int $componentId, int $versionId, int $optionId): JsonResponse
    {
        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant(Component::findOrFail($componentId));

        // 수정
        $optionData = ComponentOption::findOrFail($optionId);
        $optionData->update($request->all());

        return response()->json(collect($optionData), 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/component/{component_id}/version/{version_id}/option/{option_id}",
     *      summary="컴포넌트 옵션 삭제",
     *      description="컴포넌트 옵션을 삭제합니다",
     *      operationId="ComponentOptionDestroy",
     *      tags={"컴포넌트 옵션"},
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
     * @param int $componentId
     * @param int $versionId
     * @param int $optionId
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $componentId, int $versionId, int $optionId): Response
    {
        $componentOption = ComponentOption::findOrFail($optionId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant(Component::findOrFail($componentId));

        // 컴포넌트 옵션 삭제
        $componentOption->delete();

        return response()->noContent();
    }


    /**
     * @OA\Post (
     *      path="/v1/component/{component_id}/version/{version_id}/relational-option",
     *      summary="컴포넌트 옵션 관계형 등록",
     *      description="새로운 관계형 컴포넌트 옵션을 등록합니다.",
     *      operationId="RelationalComponentOptionCreate",
     *      tags={"컴포넌트 옵션"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"component_type_id", "name", "key"},
     *              @OA\Property(property="component_type_id", ref="#/components/schemas/ComponentOption/properties/component_type_id"),
     *              @OA\Property(property="name", ref="#/components/schemas/ComponentOption/properties/name"),
     *              @OA\Property(property="key", ref="#/components/schemas/ComponentOption/properties/key"),
     *              @OA\Property(property="display_on_pc", ref="#/components/schemas/ComponentOption/properties/display_on_pc"),
     *              @OA\Property(property="display_on_mobile", ref="#/components/schemas/ComponentOption/properties/display_on_mobile"),
     *              @OA\Property(property="hideable", ref="#/components/schemas/ComponentOption/properties/hideable"),
     *              @OA\Property(property="attributes", ref="#/components/schemas/ComponentOption/properties/attributes"),
     *              @OA\Property(property="help", ref="#/components/schemas/ComponentOption/properties/help"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentOption")
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
     * @param int $componentId
     * @param int $versionId
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function relationalStore(StoreRequest $request, int $componentId, int $versionId): JsonResponse
    {
        ComponentVersion::findOrFail($versionId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant(Component::findOrFail($componentId));

        // 컴포넌트 유형 확인
        $componentType = ComponentType::findOrFail($request->input('component_type_id'));

        // 생성 및 response
        $data = array_merge(
            $request->all(),
            [
                'component_version_id' => $versionId,
                'component_type_id' => $componentType->getAttribute('id'),
            ]
        );

        // option 생성
        $option = $this->createOption($data);

        // 컴포넌트 옵션 속성 생성
        $componentType->properties->each(function ($property) use ($option) {
            ComponentOptionProperty::create([
                'component_option_id' => $option->getAttribute('id'),
                'component_type_property_id' => $property->getAttribute('id'),
                'key' => $property->getAttribute('preset'),
            ]);
        });

        // 연동 컴포넌트 옵션 속성 추가
        $option->properties;

        return response()->json($option, 201);
    }

    /**
     * Create Component Option function
     * @param array $dataArray
     * @return mixed
     */
    protected function createOption(array $dataArray)
    {
        return ComponentOption::create($dataArray)->refresh();
    }
}
