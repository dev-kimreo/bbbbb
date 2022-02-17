<?php

namespace App\Http\Controllers\UserThemes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserThemes\UserEditablePageLayouts\IndexRequest;
use App\Http\Requests\UserThemes\UserEditablePageLayouts\StoreRequest;
use App\Http\Requests\UserThemes\UserEditablePageLayouts\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\UserThemes\UserEditablePageLayout;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class UserEditablePageLayoutController extends Controller
{
    public string $exceptionEntity = "userEditablePageLayout";

    /**
     * @OA\Get (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}/layout",
     *      summary="회원 에디터 지원페이지 레이아웃 목록",
     *      description="회원 에디터 지원페이지 레이아웃 목록",
     *      operationId="UserEditablePageLayoutIndex",
     *      tags={"회원 에디터 지원페이지 레이아웃"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserEditablePageLayout")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param IndexRequest $request
     * @param int $themeId
     * @param int $editablePageId
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request, int $themeId, int $editablePageId): Collection
    {
        $layoutBuilder = UserEditablePageLayout::query();
        $layoutBuilder->where('user_editable_page_id', $editablePageId);

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
        return collect($layoutBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get());
    }

    /**
     * @OA\Get (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}/layout/{layout_id}",
     *      summary="회원 에디터 지원페이지 레이아웃 상세",
     *      description="회원 에디터 지원페이지 레이아웃 상세",
     *      operationId="UserEditablePageLayoutShow",
     *      tags={"회원 에디터 지원페이지 레이아웃"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserEditablePageLayout")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param int $themeId
     * @param int $editablePageId
     * @param int $layoutId
     * @return Collection
     */
    public function show(int $themeId, int $editablePageId, int $layoutId): Collection
    {
        return collect(UserEditablePageLayout::where(['user_editable_page_id' => $editablePageId])->findOrFail($layoutId));
    }


    /**
     * @OA\Post (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}/layout",
     *      summary="회원 에디터 지원페이지 레이아웃 등록",
     *      description="회원 에디터 지원페이지 레이아웃 등록",
     *      operationId="UserEditablePageLayoutStore",
     *      tags={"회원 에디터 지원페이지 레이아웃"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"header_component_group_id", "content_component_group_id", "footer_component_group_id"},
     *              @OA\Property(property="header_component_group_id", ref="#/components/schemas/UserEditablePageLayout/properties/header_component_group_id"),
     *              @OA\Property(property="content_component_group_id", ref="#/components/schemas/UserEditablePageLayout/properties/content_component_group_id"),
     *              @OA\Property(property="footer_component_group_id", ref="#/components/schemas/UserEditablePageLayout/properties/footer_component_group_id"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserEditablePageLayout")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param StoreRequest $request
     * @param int $themeId
     * @param int $editablePageId
     * @return JsonResponse
     */
    public function store(StoreRequest $request, int $themeId, int $editablePageId): JsonResponse
    {
        $layout = UserEditablePageLayout::create(array_merge($request->all(), [
            'user_editable_page_id' => $editablePageId
        ]));

        $layout->refresh();

        return response()->json($layout, 201);
    }


    /**
     * @OA\Patch (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}/layout/{layout_id}",
     *      summary="회원 에디터 지원페이지 레이아웃 수정",
     *      description="회원 에디터 지원페이지 레이아웃 수정",
     *      operationId="UserEditablePageLayoutUpdate",
     *      tags={"회원 에디터 지원페이지 레이아웃"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="header_component_group_id", ref="#/components/schemas/UserEditablePageLayout/properties/header_component_group_id"),
     *              @OA\Property(property="content_component_group_id", ref="#/components/schemas/UserEditablePageLayout/properties/content_component_group_id"),
     *              @OA\Property(property="footer_component_group_id", ref="#/components/schemas/UserEditablePageLayout/properties/footer_component_group_id"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserEditablePageLayout")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param UpdateRequest $request
     * @param int $themeId
     * @param int $editablePageId
     * @param int $layoutId
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $themeId, int $editablePageId, int $layoutId): JsonResponse
    {
        $layout = UserEditablePageLayout::where('user_editable_page_id', $editablePageId)->findOrFail($layoutId);
        $layout->update($request->all());

        return response()->json($layout, 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}/layout/{layout_id}",
     *      summary="회원 에디터 지원페이지 레이아웃 삭제",
     *      description="회원 에디터 지원페이지 레이아웃 삭제",
     *      operationId="UserEditablePageLayoutDestroy",
     *      tags={"회원 에디터 지원페이지 레이아웃"},
     *      @OA\Response(
     *          response=204,
     *          description="successfully",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param int $themeId
     * @param int $editablePageId
     * @param int $layoutId
     * @return Response
     */
    public function destroy(int $themeId, int $editablePageId, int $layoutId): Response
    {
        $layout = UserEditablePageLayout::where('user_editable_page_id', $editablePageId)->findOrFail($layoutId);
        $layout->delete();

        return response()->noContent();
    }


}
