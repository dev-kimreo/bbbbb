<?php

namespace App\Http\Controllers\LinkedComponents;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LinkedComponents\IndexRequest;
use App\Http\Requests\LinkedComponents\StoreRequest;
use App\Http\Requests\LinkedComponents\UpdateRequest;
use App\Models\Components\Component;
use App\Models\EditablePages\EditablePageLayout;
use App\Models\LinkedComponents\LinkedComponent;
use App\Models\LinkedComponents\LinkedComponentOption;
use App\Models\Themes\Theme;
use App\Services\ThemeService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class LinkedComponentController extends Controller
{
    private ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component",
     *      summary="연동 컴포넌트 목록",
     *      description="연동 컴포넌트 목록",
     *      operationId="LinkedComponentIndex",
     *      tags={"연동 컴포넌트"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/LinkedComponent")
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
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request, int $themeId, int $editablePageId): Collection
    {
        $editablePageLayout = EditablePageLayout::where('editable_page_id', $editablePageId)->first();

        // 권한
        if (!$this->themeService->usableAuthor(Theme::findOrFail($themeId))) {
            throw new QpickHttpException(403, 'common.forbidden');
        }

        $res = collect();

        if ($a = $request->input('filter')) {
            foreach ($a as $type) {
                switch ($type) {
                    case 'header':
                        $res['header'] = $editablePageLayout->linkedHeaderComponentGroup->linkedComponent;
                        break;
                    case 'content':
                        $res['content'] = $editablePageLayout->linkedContentComponentGroup->linkedComponent;
                        break;
                    case 'footer':
                        $res['footer'] = $editablePageLayout->linkedFooterComponentGroup->linkedComponent;
                        break;
                }
            }
        } else {
            $res['header'] = $editablePageLayout->linkedHeaderComponentGroup->linkedComponent;
            $res['content'] = $editablePageLayout->linkedContentComponentGroup->linkedComponent;
            $res['footer'] = $editablePageLayout->linkedFooterComponentGroup->linkedComponent;
        }

        return $res;
    }

    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}",
     *      summary="연동 컴포넌트 상세",
     *      description="연동 컴포넌트 상세정보",
     *      operationId="LinkedComponentShow",
     *      tags={"연동 컴포넌트"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"linked_component_group_id", "component_id"},
     *              @OA\Property(
     *                  property="withRenderData",
     *                  type="boolean",
     *                  example="1",
     *                  description="1일 경우, Template, Stylesheet 소스코드와 Script Request URL을 함께 반환"
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              allOf={@OA\Schema(ref="#/components/schemas/LinkedComponent")},
     *              @OA\Property(property="renderData", type="object",
     *                  @OA\Property(
     *                      property="template",
     *                      type="string",
     *                      example="<div></div>",
     *                      description="컴포넌트의 HTML 소스코드"
     *                  ),
     *                  @OA\Property(
     *                      property="style",
     *                      type="string",
     *                      example="div{width:100%}",
     *                      description="컴포넌트의 CSS 소스코드"
     *                  ),
     *                  @OA\Property(
     *                      property="script",
     *                      type="url",
     *                      example="http://local-api.qpikci.com/script",
     *                      description="Script Request URL"
     *                  )
     *              )
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
     */
    public function show(int $themeId, int $editablePageId, int $linkedComponentId)
    {
        $res = LinkedComponent::query()->findOrFail($linkedComponentId);

        if (request()->input('with_render_data')) {
            $res = $res->setAppends(['renderData']);
        }

        return $res;
    }

    /**
     * @OA\Post (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component",
     *      summary="연동 컴포넌트 등록",
     *      description="연동 컴포넌트를 등록합니다.",
     *      operationId="LinkedComponentCreate",
     *      tags={"연동 컴포넌트"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"linked_component_group_id", "component_id"},
     *              @OA\Property(property="linked_component_group_id", ref="#/components/schemas/LinkedComponent/properties/linked_component_group_id"),
     *              @OA\Property(property="component_id", ref="#/components/schemas/LinkedComponent/properties/component_id"),
     *              @OA\Property(property="name", ref="#/components/schemas/LinkedComponent/properties/name"),
     *              @OA\Property(property="sort", ref="#/components/schemas/LinkedComponent/properties/sort"),
     *              @OA\Property(property="display_on_pc", ref="#/components/schemas/LinkedComponent/properties/display_on_pc"),
     *              @OA\Property(property="display_on_mobile", ref="#/components/schemas/LinkedComponent/properties/display_on_mobile"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/LinkedComponent")
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
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, int $themeId, int $editablePageId): JsonResponse
    {
        $linkedComponent = $this->createLinkedComponent(Theme::findOrFail($themeId), $request);

        return response()->json(collect($linkedComponent), 201);
    }


    /**
     * @OA\Patch (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}",
     *      summary="연동 컴포넌트 수정",
     *      description="연동 컴포넌트를 수정합니다.",
     *      operationId="LinkedComponentUpdate",
     *      tags={"연동 컴포넌트"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="linked_component_group_id", ref="#/components/schemas/LinkedComponent/properties/linked_component_group_id"),
     *              @OA\Property(property="name", ref="#/components/schemas/LinkedComponent/properties/name"),
     *              @OA\Property(property="sort", ref="#/components/schemas/LinkedComponent/properties/sort"),
     *              @OA\Property(property="display_on_pc", ref="#/components/schemas/LinkedComponent/properties/display_on_pc"),
     *              @OA\Property(property="display_on_mobile", ref="#/components/schemas/LinkedComponent/properties/display_on_mobile"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/LinkedComponent")
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
     * @throws QpickHttpException
     */
    public function update(UpdateRequest $request, int $themeId, int $editablePageId, int $linkedComponentId): JsonResponse
    {
        // 테마 작성자 확인
        if (!$this->themeService->usableAuthor(Theme::findOrFail($themeId))) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $linkedComponent = LinkedComponent::findOrFail($linkedComponentId);

        // 컴포넌트 작성자 확인
        if (!Auth::user()->can('authorize', $component = Component::findOrFail($linkedComponent->getAttribute('component_id')))) {
            throw new QpickHttpException(403, 'common.forbidden');
        }

        // check linkedComponentGroupId
        if ($i = $request->input('linked_component_group_id')) {
            $editablePageLayout = EditablePageLayout::query()->where('editable_page_id', $editablePageId)->first();
            $eplData = $editablePageLayout->getAttributes();
            if (!in_array($i, [$eplData['header_component_group_id'], $eplData['content_component_group_id'], $eplData['footer_component_group_id']])) {
                throw new QpickHttpException(422, 'common.bad_request');
            }
        }

        $linkedComponent->update($request->all());

        return response()->json(collect($linkedComponent), 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}",
     *      summary="연동 컴포넌트 삭제",
     *      description="연동 컴포넌트를 삭제합니다",
     *      operationId="LinkedComponentDestroy",
     *      tags={"연동 컴포넌트"},
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
     * @throws QpickHttpException
     */
    public function destroy(int $themeId, int $editablePageId, int $linkedComponentId): Response
    {
        // 테마 작성자 확인
        if (!$this->themeService->usableAuthor(Theme::findOrFail($themeId))) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $linkedComponent = LinkedComponent::findOrFail($linkedComponentId);

        // 컴포넌트 작성자 확인
        if (!Auth::user()->can('authorize', $component = Component::findOrFail($linkedComponent->getAttribute('component_id')))) {
            throw new QpickHttpException(403, 'common.forbidden');
        }

        $linkedComponent->delete();


        return response()->noContent();
    }

    /**
     * @OA\Post (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/relational-linked-component",
     *      summary="연동 컴포넌트 관계형 등록",
     *      description="연동 컴포넌트 등록 / 관계형 옵션을 등록합니다.",
     *      operationId="RelationalLinkedComponentCreate",
     *      tags={"연동 컴포넌트"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"linked_component_group_id", "component_id"},
     *              @OA\Property(property="linked_component_group_id", ref="#/components/schemas/LinkedComponent/properties/linked_component_group_id"),
     *              @OA\Property(property="component_id", ref="#/components/schemas/LinkedComponent/properties/component_id"),
     *              @OA\Property(property="name", ref="#/components/schemas/LinkedComponent/properties/name"),
     *              @OA\Property(property="sort", ref="#/components/schemas/LinkedComponent/properties/sort"),
     *              @OA\Property(property="display_on_pc", ref="#/components/schemas/LinkedComponent/properties/display_on_pc"),
     *              @OA\Property(property="display_on_mobile", ref="#/components/schemas/LinkedComponent/properties/display_on_mobile"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully"
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
     * @throws QpickHttpException
     */
    public function relationalLinkedComponent(StoreRequest $request, int $themeId, int $editablePageId)
    {
        $linkedComponent = $this->createLinkedComponent(Theme::findOrFail($themeId), $request);

        // 컴포넌트에 적용된 옵션 연동 컴포넌트 옵션으로 추가
        $this->createLinkedComponentOptionForComponent($linkedComponent);
    }

    /**
     * @throws QpickHttpException
     */
    protected function createLinkedComponent(Theme $theme, StoreRequest $request)
    {
        // 테마 작성자 확인
        if (!$this->themeService->usableAuthor($theme)) {
            throw new QpickHttpException(403, 'common.forbidden');
        }

        // 컴포넌트 작성자 확인
        if (!Auth::user()->can('authorize', $component = Component::findOrFail($request->input('component_id')))) {
            throw new QpickHttpException(403, 'common.forbidden');
        }

        // Sort 값 설정
        $maxSortLinkedComponent = LinkedComponent::selectRaw('max(sort) as sort')->first();
        $maxSort = $maxSortLinkedComponent ? $maxSortLinkedComponent->getAttribute('sort') + 1 : 1;

        // Linked Component 생성
        return LinkedComponent::create(array_merge(
            [
                'name' => $component->getAttribute('name'),
                'sort' => $maxSort
            ],
            $request->all()
        ))->refresh();
    }

    protected function createLinkedComponentOptionForComponent(LinkedComponent $linkedComponent)
    {
        $linkedComponent->component()->each(function ($c) use ($linkedComponent) {
            $c->usableVersion()->each(function ($uv) use ($linkedComponent) {
                $uv->option->each(function ($item) use ($linkedComponent) {
                    LinkedComponentOption::create([
                        'component_option_id' => $item->getAttribute('id'),
                        'linked_component_id' => $linkedComponent->getAttribute('id')
                    ]);
                });
            });
        });
    }

    public function scriptRequest()
    {
    }
}
