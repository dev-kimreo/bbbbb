<?php

namespace App\Http\Controllers\UserThemes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserThemes\UserEditablePages\IndexRequest;
use App\Http\Requests\UserThemes\UserEditablePages\StoreRequest;
use App\Http\Requests\UserThemes\UserEditablePages\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\UserThemes\UserEditablePage;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class UserEditablePageController extends Controller
{
    public string $exceptionEntity = "userEditablePage";

    /**
     * @OA\Get (
     *      path="/v1/user-theme/{user_theme_id}/editable-page",
     *      summary="회원 에디터 지원페이지 목록",
     *      description="회원 에디터 지원페이지 목록",
     *      operationId="UserEditablePageIndex",
     *      tags={"회원 에디터 지원페이지"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserEditablePage")
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
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request, int $themeId): Collection
    {
        $editablePageBuilder = UserEditablePage::query();
        $editablePageBuilder->where('user_theme_id', $themeId);

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($editablePageBuilder) {
                $editablePageBuilder->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $editablePageBuilder->count(), $request->input('per_page'));

        // get data
        return collect($editablePageBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get());
    }

    /**
     * @OA\Get (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}",
     *      summary="회원 에디터 지원페이지 상세",
     *      description="회원 에디터 지원페이지 상세",
     *      operationId="UserEditablePageShow",
     *      tags={"회원 에디터 지원페이지"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserEditablePage")
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
     * @return Collection
     */
    public function show(int $themeId, int $editablePageId): Collection
    {
        return collect(UserEditablePage::where(['user_theme_id' => $themeId])->findOrFail($editablePageId));
    }


    /**
     * @OA\Post (
     *      path="/v1/user-theme/{user_theme_id}/editable-page",
     *      summary="회원 에디터 지원페이지 등록",
     *      description="회원 에디터 지원페이지 등록",
     *      operationId="UserEditablePageStore",
     *      tags={"회원 에디터 지원페이지"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"supported_editable_page_id"},
     *              @OA\Property(property="supported_editable_page_id", ref="#/components/schemas/UserEditablePage/properties/supported_editable_page_id"),
     *              @OA\Property(property="name", ref="#/components/schemas/UserEditablePage/properties/name"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserEditablePage")
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
     * @return JsonResponse
     */
    public function store(StoreRequest $request, int $themeId): JsonResponse
    {
        $editablePage = UserEditablePage::create(array_merge($request->all(), [
            'user_theme_id' => $themeId
        ]));

        $editablePage->refresh();

        return response()->json($editablePage, 201);
    }


    /**
     * @OA\Patch (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}",
     *      summary="회원 에디터 지원페이지 수정",
     *      description="회원 에디터 지원페이지 수정",
     *      operationId="UserEditablePageUpdate",
     *      tags={"회원 에디터 지원페이지"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/UserEditablePage/properties/name"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserEditablePage")
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
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $themeId, int $editablePageId): JsonResponse
    {
        $editablePage = UserEditablePage::where('user_theme_id', $themeId)->findOrFail($editablePageId);
        $editablePage->update($request->all());

        return response()->json($editablePage, 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/user-theme/{user_theme_id}/editable-page/{editable_page_id}",
     *      summary="회원 에디터 지원페이지 삭제",
     *      description="회원 에디터 지원페이지 삭제",
     *      operationId="UserEditablePageDestroy",
     *      tags={"회원 에디터 지원페이지"},
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
     * @return Response
     */
    public function destroy(int $themeId, int $editablePageId): Response
    {
        $editablePage = UserEditablePage::where('user_theme_id', $themeId)->findOrFail($editablePageId);
        $editablePage->delete();

        return response()->noContent();
    }


}
