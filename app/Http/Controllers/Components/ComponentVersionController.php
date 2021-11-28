<?php

namespace App\Http\Controllers\Components;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Components\Versions\IndexRequest;
use App\Http\Requests\Components\Versions\ShowRequest;
use App\Http\Requests\Components\Versions\StoreRequest;
use App\Http\Requests\Components\Versions\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Components\Component;
use App\Models\Components\ComponentVersion;
use App\Services\ComponentService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ComponentVersionController extends Controller
{

    public function __construct()
    {

    }

    /**
     * @OA\Get (
     *      path="/v1/component/{component_id}/version",
     *      summary="컴포넌트 버전 목록",
     *      description="컴포넌트 버전 목록",
     *      operationId="ComponentVersionIndex",
     *      tags={"컴포넌트 버전"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ComponentVersion")
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
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request, int $componentId): Collection
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        $componentVersionBuilder = ComponentVersion::query()->where(['component_id' => $component->getAttribute('id'), 'usable' => 1]);

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($componentVersionBuilder) {
                $componentVersionBuilder->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $componentVersionBuilder->count(), $request->input('per_page'));

        // get data
        return collect($componentVersionBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get());
    }

    /**
     * @OA\Get (
     *      path="/v1/component/{component_id}/version/{version_id}",
     *      summary="컴포넌트 버전 상세",
     *      description="컴포넌트 버전 상세정보",
     *      operationId="ComponentVersionShow",
     *      tags={"컴포넌트 버전"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentVersion")
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
     * @param int $componentId
     * @param int $versionId
     * @return Collection
     * @throws QpickHttpException
     */
    public function show(ShowRequest $request, int $componentId, int $versionId): Collection
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        $res = ComponentVersion::where(['component_id' => $component->getAttribute('id'), 'usable' => 1])->findOrFail($versionId);

        return collect($res);
    }

    /**
     * @OA\Post (
     *      path="/v1/component/{component_id}/version",
     *      summary="컴포넌트 버전 등록",
     *      description="새로운 컴포넌트 버전을 등록합니다.",
     *      operationId="ComponentVersionCreate",
     *      tags={"컴포넌트 버전"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"icon"},
     *              @OA\Property(property="template", ref="#/components/schemas/ComponentVersion/properties/template"),
     *              @OA\Property(property="script", ref="#/components/schemas/ComponentVersion/properties/script"),
     *              @OA\Property(property="style", ref="#/components/schemas/ComponentVersion/properties/style"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentVersion")
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
     * @param int $componentId
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, int $componentId): JsonResponse
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        // 등록 된 컴포넌트 버전 수량 확인
        $this->checkCreatableVersion($component);

        // 생성 및 response
        return response()->json(
            collect(
                ComponentVersion::create(array_merge(
                    $request->all(),
                    [
                        'component_id' => $component->getAttribute('id')
                    ]
                ))->refresh()
            )
        );
    }

    /**
     * @OA\Patch (
     *      path="/v1/component/{component_id}/version/{version_id}",
     *      summary="컴포넌트 버전 수정",
     *      description="컴포넌트 버전을 수정합니다.",
     *      operationId="ComponentVersionUpdate",
     *      tags={"컴포넌트 버전"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="template", ref="#/components/schemas/ComponentVersion/properties/template"),
     *              @OA\Property(property="script", ref="#/components/schemas/ComponentVersion/properties/script"),
     *              @OA\Property(property="style", ref="#/components/schemas/ComponentVersion/properties/style"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentVersion")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "partner_auth":{}
     *      }}
     *  )
     * @param UpdateRequest $request
     * @param int $componentId
     * @param int $versionId
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function update(UpdateRequest $request, int $componentId, int $versionId): JsonResponse
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        // 컴포넌트 버전 수정
        $versionData = ComponentVersion::findOrFail($versionId);
        $versionData->update($request->all());

        return response()->json(collect($versionData), 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/component/{component_id}/version/{version_id}",
     *      summary="컴포넌트 버전 삭제",
     *      description="컴포넌트 버전을 삭제합니다",
     *      operationId="ComponentVersionDestroy",
     *      tags={"컴포넌트 버전"},
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
     * @param int $versionId
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $componentId, int $versionId): Response
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        $versionData = ComponentVersion::findOrFail($versionId);

        // 컴포넌트 버전 사용 중일 경우 삭제 불가능
        if ($versionData->getAttribute('usable')) {
            throw new QpickHttpException(422, 'component_version.disable.destroy.in_use');
        }

        // 삭제
        $versionData->delete();

        return response()->noContent();
    }


    /**
     * @OA\Patch (
     *      path="/v1/component/{component_id}/activate-version/{version_id}",
     *      summary="특정 컴포넌트 버전 활성화",
     *      description="특정 컴포넌트 버전을 활성화하고, 다른 버전은 비활성화 한다.",
     *      operationId="ComponentVersionActivate",
     *      tags={"컴포넌트 버전"},
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentVersion")
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
     * @param int $versionId
     * @return JsonResponse
     * @throws QpickHttpException
     * 특정 컴포넌트 버전의 활성화 및 다른 버전은 비활성화
     */
    public function activate(int $componentId, int $versionId): JsonResponse
    {
        $component = Component::findOrFail($componentId);

        // 컴포넌트 권한 확인
        ComponentService::checkRegistrant($component);

        // 활성화되있던 버전 가져와서 비활성화
        ComponentVersion::query()->where([
            'component_id' => $component->getAttribute('id'),
            'usable' => 1
        ])->update(['usable' => 0]);

        // 활성화 대상 버전 활성화
        $currentVersion = ComponentVersion::findOrFail($versionId);
        $currentVersion->update(['usable' => 1]);

        return response()->json(collect($currentVersion), 201);
    }

    public function duplicate(int $componentId, int $versionId)
    {

    }


    /**
     * @param $componentData
     * @throws QpickHttpException
     */
    protected function checkCreatableVersion($componentData)
    {
        $componentCount = ComponentVersion::query()->where([
            'component_id' => $componentData->getAttribute('id')
        ])->count();

        if (ComponentVersion::$limitCount <= $componentCount) {
            throw new QpickHttpException(422, 'component_version.disable.create.limited_count_over');
        }
    }


}
