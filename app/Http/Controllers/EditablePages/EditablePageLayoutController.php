<?php

namespace App\Http\Controllers\EditablePages;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\EditablePages\Layouts\IndexRequest;
use App\Http\Requests\EditablePages\Layouts\StoreRequest;
use App\Http\Requests\EditablePages\Layouts\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\EditablePages\EditablePageLayout;
use App\Models\Themes\Theme;
use App\Services\EditorService;
use App\Services\ThemeService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EditablePageLayoutController extends Controller
{
    private ThemeService $themeService;
    private EditorService $editorService;

    public function __construct(ThemeService $themeService, EditorService $editorService)
    {
        $this->themeService = $themeService;
        $this->editorService = $editorService;
    }

    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/layout",
     *      summary="에디터 지원 페이지 레이아웃 목록",
     *      description="에디터 지원 페이지 레이아웃 목록",
     *      operationId="EditablePageLayoutIndex",
     *      tags={"에디터 지원 페이지 레이아웃"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/EditablePageLayout")
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
    public function index(IndexRequest $request)
    {
        $layoutBuilder = EditablePageLayout::query();

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($layoutBuilder) {
                $layoutBuilder->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $layoutBuilder->count(), $request->input('per_page'));

        // get data
        return $layoutBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get();
    }

    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/layout/{layout_id}",
     *      summary="에디터 지원 페이지 레이아웃 상세",
     *      description="에디터 지원 페이지 레이아웃 상세정보",
     *      operationId="EditablePageLayoutShow",
     *      tags={"에디터 지원 페이지 레이아웃"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/EditablePageLayout")
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
    public function show(int $layoutId)
    {
        return EditablePageLayout::findOrFail($layoutId);
    }


    /**
     * @OA\Post (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/layout",
     *      summary="에디터 지원 페이지 레이아웃 등록",
     *      description="새로운 에디터 지원 페이지 레이아웃을 등록합니다.",
     *      operationId="EditablePageLayoutCreate",
     *      tags={"에디터 지원 페이지 레이아웃"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"header_component_group_id", "content_component_group_id", "footer_component_group_id"},
     *              @OA\Property(property="header_component_group_id", ref="#/components/schemas/EditablePageLayout/properties/header_component_group_id"),
     *              @OA\Property(property="content_component_group_id", ref="#/components/schemas/EditablePageLayout/properties/content_component_group_id"),
     *              @OA\Property(property="footer_component_group_id", ref="#/components/schemas/EditablePageLayout/properties/footer_component_group_id"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/EditablePageLayout")
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
        $layout = $this->editorService->createEditablePageLayout(Theme::findOrFail($themeId), $editablePageId, $request->all());

        return response()->json(collect($layout), 201);
    }

    /**
     * @OA\Patch (
     *      path="/v1/theme/{theme_id}/editablePage/{editable_page_id}/layout/{layout_id}",
     *      summary="에디터 지원 페이지 레이아웃 수정",
     *      description="에디터 지원 페이지 레이아웃을 수정합니다.",
     *      operationId="editablePageLayoutUpdate",
     *      tags={"에디터 지원 페이지 레이아웃"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="header_component_group_id", ref="#/components/schemas/EditablePageLayout/properties/header_component_group_id"),
     *              @OA\Property(property="content_component_group_id", ref="#/components/schemas/EditablePageLayout/properties/content_component_group_id"),
     *              @OA\Property(property="footer_component_group_id", ref="#/components/schemas/EditablePageLayout/properties/footer_component_group_id"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/EditablePageLayout")
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
    public function update(UpdateRequest $request, int $themeId, int $editablePageId, int $layoutId): JsonResponse
    {
        $layout = EditablePageLayout::findOrFail($layoutId);

        if (!$this->themeService->usableAuthor(Theme::findOrFail($themeId))) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $layout->update($request->all());

        return response()->json(collect($layout), 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/theme/{theme_id}/editablePage/{editable_page_id}/layout/{layout_id}",
     *      summary="에디터 지원 페이지 레이아웃 삭제",
     *      description="에디터 지원 페이지 레이아웃을 삭제합니다",
     *      operationId="editablePageLayoutDestroy",
     *      tags={"에디터 지원 페이지 레이아웃"},
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
    public function destroy(int $themeId, int $editablePageId, int $layoutId): Response
    {
        $layout = EditablePageLayout::findOrFail($layoutId);

        if (!$this->themeService->usableAuthor(Theme::findOrFail($themeId))) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $layout->delete();

        return response()->noContent();
    }


}
