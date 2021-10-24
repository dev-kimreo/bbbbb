<?php

namespace App\Http\Controllers\EditablePages;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\EditablePages\IndexRequest;
use App\Http\Requests\EditablePages\ShowRequest;
use App\Http\Requests\EditablePages\StoreRequest;
use App\Http\Requests\EditablePages\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\EditablePages\EditablePage;
use App\Models\SupportedEditablePage;
use App\Models\Themes\Theme;
use App\Services\ThemeService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EditablePageController extends Controller
{
    private ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }


    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page",
     *      summary="에디터 지원 페이지 목록",
     *      description="에디터 지원 페이지 목록",
     *      operationId="EditablePageIndex",
     *      tags={"에디터 지원 페이지"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/EditablePage")
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
    public function index(IndexRequest $request)
    {
        $editableBuilder = EditablePage::query();

        if ($i = $request->route('theme_id')) {
            $editableBuilder->where('theme_id', $i);
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($editableBuilder) {
                $editableBuilder->orderBy($item['key'], $item['value']);
            });
        } else {
            $editableBuilder->orderBy('supported_editable_page_id', 'asc');
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $editableBuilder->count(), $request->input('per_page'));

        // get data
        return $editableBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get();
    }

    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}",
     *      summary="에디터 지원 페이지 상세",
     *      description="에디터 지원 페이지 상세정보",
     *      operationId="EditablePageShow",
     *      tags={"에디터 지원 페이지"},
     *      @OA\Response(
     *          response=200,
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
     */
    public function show(int $theme_id, int $editable_page_id)
    {
        return EditablePage::where('theme_id', $theme_id)->findOrFail($editable_page_id);
    }


    /**
     * @OA\Post (
     *      path="/v1/theme/{theme_id}/editable-page",
     *      summary="에디터 지원 페이지 등록",
     *      description="새로운 에디터 지원페이지를 등록합니다.",
     *      operationId="EditablePageCreate",
     *      tags={"에디터 지원 페이지"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"solution_id"},
     *              @OA\Property(property="supported_editable_page_id", ref="#/components/schemas/EditablePage/properties/supported_editable_page_id"),
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
     *
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, int $theme_id): JsonResponse
    {
        $supportedEditablePageId = $request->input('supported_editable_page_id');
        $theme = Theme::findOrFail($theme_id);

        // check policy
        if (!$this->themeService->usableAuthor($theme)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // exists supported page
        if (EditablePage::where(['theme_id' => $theme_id, 'supported_editable_page_id' => $supportedEditablePageId])->exists()) {
            throw new QpickHttpException(422, 'supported_editable_page.disable.already_exists');
        }

        // check solution
        $supportedEditablePage = SupportedEditablePage::find($supportedEditablePageId);
        if ($theme->getAttribute('solution_id') != $supportedEditablePage->getAttribute('solution_id')) {
            throw new QpickHttpException(422, 'supported_editable_page.disable.add_to_selected_theme');
        }

        $editablePage = EditablePage::create(
            array_merge(
                $request->all(),
                [
                    'theme_id' => $theme_id,
                    'supported_editable_page_id' => $supportedEditablePageId
                ]
            )
        )->refresh();

        return response()->json(collect($editablePage), 201);
    }


    /**
     * @OA\Patch (
     *      path="/v1/theme/{theme_id}/editablePage/{editable_page_id}",
     *      summary="에디터 지원 페이지 수정",
     *      description="에디터 지원 페이지를 수정합니다.",
     *      operationId="editablePageUpdate",
     *      tags={"에디터 지원 페이지"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/EditablePage/properties/name"),
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
     *
     * @throws QpickHttpException
     */
    public function update(UpdateRequest $request, int $theme_id, int $editable_page_id): JsonResponse
    {
        // check policy
        if (!$this->themeService->usableAuthor(Theme::findOrFail($theme_id))) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $editablePage = EditablePage::where('theme_id', $theme_id)->findOrFail($editable_page_id);
        $editablePage->update($request->all());

        return response()->json(collect($editablePage), 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/theme/{theme_id}/editablePage/{editable_page_id}",
     *      summary="에디터 지원 페이지 삭제",
     *      description="에디터 지원 페이지를 삭제합니다",
     *      operationId="editablePageDestroy",
     *      tags={"에디터 지원 페이지"},
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
    public function destroy(int $theme_id, int $editable_page_id): Response
    {
        // check policy
        if (!$this->themeService->usableAuthor(Theme::findOrFail($theme_id))) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $editablePage = EditablePage::where('theme_id', $theme_id)->findOrFail($editable_page_id);
        $editablePage->delete();

        return response()->noContent();
    }

}
