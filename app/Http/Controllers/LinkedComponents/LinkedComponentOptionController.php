<?php

namespace App\Http\Controllers\LinkedComponents;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LinkedComponents\Options\IndexRequest;
use App\Http\Requests\LinkedComponents\Options\StoreRequest;
use App\Http\Requests\LinkedComponents\Options\UpdateRequest;
use App\Models\LinkedComponents\LinkedComponentOption;
use App\Services\ThemeService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LinkedComponentOptionController extends Controller
{
    private ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option",
     *      summary="연동 컴포넌트 옵션 목록",
     *      description="연동 컴포넌트 옵션 목록",
     *      operationId="LinkedComponentOptionIndex",
     *      tags={"연동 컴포넌트 옵션"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/LinkedComponentOption")
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
    public function index(IndexRequest $request, int $themeId, int $editablePageId, int $linkedComponentId)
    {
        return LinkedComponentOption::where('linked_component_id', $linkedComponentId)->get();
    }


    /**
     * @OA\Get (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{linked_component_option_id}",
     *      summary="연동 컴포넌트 옵션 상세",
     *      description="연동 컴포넌트 옵션 상세정보",
     *      operationId="LinkedComponentOptionShow",
     *      tags={"연동 컴포넌트 옵션"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/LinkedComponentOption")
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
    public function show(int $themeId, int $editablePageId, int $linkedComponentId, int $linkedComponentOptionId)
    {
        return LinkedComponentOption::findOrFail($linkedComponentOptionId);
    }


    /**
     * @OA\Post (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option",
     *      summary="연동 컴포넌트 옵션 등록",
     *      description="연동 컴포넌트 옵션을 등록합니다.",
     *      operationId="LinkedComponentOptionCreate",
     *      tags={"연동 컴포넌트 옵션"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"component_option_id"},
     *              @OA\Property(property="component_option_id", ref="#/components/schemas/LinkedComponentOption/properties/component_option_id"),
     *              @OA\Property(property="value", ref="#/components/schemas/LinkedComponentOption/properties/value"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/LinkedComponentOption")
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
    public function store(StoreRequest $request, int $themeId, int $editablePageId, int $linkedComponentId): JsonResponse
    {
        $linkedComponentOption = LinkedComponentOption::create(array_merge(
            $request->all(),
            [
                'linked_component_id' => $linkedComponentId
            ]
        ))->refresh();

        return response()->json(collect($linkedComponentOption), 201);
    }


    /**
     * @OA\Patch (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{option_id}",
     *      summary="연동 컴포넌트 옵션 수정",
     *      description="연동 컴포넌트 옵션을 수정합니다.",
     *      operationId="LinkedComponentOptionUpdate",
     *      tags={"연동 컴포넌트 옵션"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="value", ref="#/components/schemas/LinkedComponentOption/properties/value"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/LinkedComponentOption")
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
    public function update(UpdateRequest $request, int $themeId, int $editablePageId, int $linkedComponentId, int $linkedComponentOptionId): JsonResponse
    {
        $linkedComponentOption = LinkedComponentOption::findOrFail($linkedComponentOptionId);
        $linkedComponentOption->update($request->all());

        return response()->json(collect($linkedComponentOption), 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/theme/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{option_id}",
     *      summary="연동 컴포넌트 옵션 삭제",
     *      description="연동 컴포넌트 옵션을 삭제합니다",
     *      operationId="LinkedComponentOptionDestroy",
     *      tags={"연동 컴포넌트 옵션"},
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
     */
    public function destroy(int $themeId, int $editablePageId, int $linkedComponentId, int $linkedComponentOptionId): Response
    {
        $linkedComponentOption = LinkedComponentOption::findOrFail($linkedComponentOptionId);
        $linkedComponentOption->delete();

        return response()->noContent();
    }

}
