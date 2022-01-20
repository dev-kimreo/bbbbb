<?php

namespace App\Http\Controllers\Components;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Components\UsablePages\IndexRequest;
use App\Http\Requests\Components\UsablePages\StoreRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Components\ComponentUsablePage;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ComponentUsablePageController extends Controller
{
    public string $exceptionEntity = "componentUsablePage";

    public function __construct()
    {
    }


    /**
     * @OA\Get (
     *      path="/v1/component-usable-page",
     *      summary="컴포넌트 사용 페이지 목록",
     *      description="컴포넌트 사용 페이지 목록",
     *      operationId="ComponentUsablePageIndex",
     *      tags={"컴포넌트 사용 페이지"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ComponentUsablePage")
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
     * @param IndexRequest $request
     * @return Collection
     */
    public function index(IndexRequest $request): Collection
    {
        $page = ComponentUsablePage::query();

        if ($i = $request->input('component_id')) {
            $page->where('component_id', $i);
        }

        if ($i = $request->input('supported_editable_page_id')) {
            $page->where('supported_editable_page_id', $i);
        }

        return collect($page->get());
    }

    /**
     * @OA\Get (
     *      path="/v1/component-usable-page/{component_usable_page_id}",
     *      summary="컴포넌트 사용 페이지 상세",
     *      description="컴포넌트 사용 페이지 상세정보",
     *      operationId="ComponentUsablePageShow",
     *      tags={"컴포넌트 사용 페이지"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentUsablePage")
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
     * @param int $componentUsablePageId
     * @return Collection
     */
    public function show(int $componentUsablePageId): Collection
    {
        return collect(ComponentUsablePage::findOrFail($componentUsablePageId));
    }

    /**
     * @OA\Post (
     *      path="/v1/component-usable-page",
     *      summary="컴포넌트 사용 페이지 등록",
     *      description="컴포넌트 사용페이지를 등록합니다.",
     *      operationId="ComponentUsablePageCreate",
     *      tags={"컴포넌트 사용 페이지"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"component_id", "supported_editable_page_id"},
     *              @OA\Property(property="component_id", ref="#/components/schemas/ComponentUsablePage/properties/component_id"),
     *              @OA\Property(property="supported_editable_page_id", ref="#/components/schemas/ComponentUsablePage/properties/supported_editable_page_id"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ComponentUsablePage")
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
        $page = ComponentUsablePage::create($request->all());

        $page->refresh();

        return response()->json(collect($page), 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/component-usable-page/{component_usable_page_id}",
     *      summary="컴포넌트 사용 페이지 삭제",
     *      description="컴포넌트 사용 페이지를 삭제합니다",
     *      operationId="ComponentUsablePageDestroy",
     *      tags={"컴포넌트 사용 페이지"},
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
     * @param int $componentUsablePageId
     * @return Response
     */
    public function destroy(int $componentUsablePageId): Response
    {
        $page = ComponentUsablePage::findOrFail($componentUsablePageId);

        $page->delete();

        return response()->noContent();
    }
}
