<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Themes\DestroyRequest;
use App\Http\Requests\Themes\IndexRequest;
use App\Http\Requests\Themes\ShowRequest;
use App\Http\Requests\Themes\StoreRequest;
use App\Http\Requests\Themes\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Themes\Theme;
use App\Models\Themes\ThemeProduct;
use App\Services\EditorService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ThemeController extends Controller
{
    protected Theme $theme;
    private EditorService $editorService;
    public string $exceptionEntity = "theme";

    public function __construct(Theme $theme, EditorService $editorService)
    {
        $this->theme = $theme;
        $this->editorService = $editorService;
    }

    /**
     * @OA\Get (
     *      path="/v1/theme-product/{theme_product_id}/theme",
     *      summary="테마 목록",
     *      description="테마 목록",
     *      operationId="themeIndex",
     *      tags={"테마"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Theme")
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
    public function index(IndexRequest $request): Collection
    {
        $theme = Theme::query();

        if ($i = $request->route('theme_product_id')) {
            $theme->where(['theme_product_id' => $i]);
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id', 'solution_id']);
            $sortCollect->each(function ($item) use ($theme) {
                $theme->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $theme->count(), $request->input('per_page'));

        // get data
        return collect($theme->skip($pagination['skip'])->take($pagination['perPage'])->get());
    }


    /**
     * @OA\Get (
     *      path="/v1/theme-product/{theme_product_id}/theme/{theme_id}",
     *      summary="테마 상세",
     *      description="테마 상세정보",
     *      operationId="themeShow",
     *      tags={"테마"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Theme")
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
    public function show(ShowRequest $request): Collection
    {
        $theme = Theme::query();

        if ($i = $request->route('theme_product_id')) {
            $theme->where('theme_product_id', $i);
        }

        return collect($theme->findOrFail($request->route('theme_id')));
    }


    /**
     * @OA\Post (
     *      path="/v1/theme-product/{theme_product_id}/theme",
     *      summary="테마 등록",
     *      description="새로운 테마를 등록합니다.",
     *      operationId="themeCreate",
     *      tags={"테마"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"solution_id"},
     *              @OA\Property(property="solution_id", ref="#/components/schemas/Theme/properties/solution_id"),
     *              @OA\Property(property="status", ref="#/components/schemas/Theme/properties/status"),
     *              @OA\Property(property="display", ref="#/components/schemas/Theme/properties/display")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Theme")
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
    public function store(StoreRequest $request): JsonResponse
    {
        return response()->json(collect($this->createTheme($request)), 201);
    }

    /**
     * @OA\Patch (
     *      path="/v1/theme-product/{theme_product_id}/theme/{theme_id}",
     *      summary="테마 수정",
     *      description="테마를 수정합니다.",
     *      operationId="themeUpdate",
     *      tags={"테마"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="status", ref="#/components/schemas/Theme/properties/status"),
     *              @OA\Property(property="display", ref="#/components/schemas/Theme/properties/display")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Theme")
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
    public function update(UpdateRequest $request): JsonResponse
    {
        $themeProductId = $request->route('theme_product_id');
        $themeId = $request->route('theme_id');

        $themeBuilder = Theme::query();

        if ($themeProductId) {
            $themeBuilder->where('theme_product_id', $themeProductId);
        }

        $theme = $themeBuilder->findOrFail($themeId);

        // check update policy
        $themeProduct = ThemeProduct::find($theme->getAttribute('theme_product_id'));
        if (!Auth::user()->can('update', $themeProduct)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $theme->update($request->all());

        return response()->json(collect($theme), 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/theme-product/{theme_product_id}/theme/{theme_id}",
     *      summary="테마 삭제",
     *      description="테마를 삭제합니다",
     *      operationId="themeDestroy",
     *      tags={"테마"},
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
     * @throws QpickHttpException
     */
    public function destroy(DestroyRequest $request): Response
    {
        $themeProductId = $request->route('theme_product_id');
        $themeId = $request->route('theme_id');

        $themeBuilder = Theme::query();

        if ($themeProductId) {
            $themeBuilder->where('theme_product_id', $themeProductId);
        }

        $theme = $themeBuilder->findOrFail($themeId);

        // check delete policy
        $themeProduct = ThemeProduct::find($theme->getAttribute('theme_product_id'));
        if (!Auth::user()->can('delete', $themeProduct)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $theme->delete();

        return response()->noContent();
    }


    /**
     * @OA\Post (
     *      path="/v1/theme-product/{theme_product_id}/relational-theme",
     *      summary="테마 관계형 등록",
     *      description="테마를 등록하고 하위 관계 요소들을 자동 생성합니다.",
     *      operationId="relationalThemeCreate",
     *      tags={"테마"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"solution_id"},
     *              @OA\Property(property="solution_id", ref="#/components/schemas/Theme/properties/solution_id"),
     *              @OA\Property(property="status", ref="#/components/schemas/Theme/properties/status"),
     *              @OA\Property(property="display", ref="#/components/schemas/Theme/properties/display")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Theme")
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
    public function relationalStore(StoreRequest $request, int $theme_product_id): JsonResponse
    {
        // 테마 생성
        $theme = $this->createTheme($request);

        // 솔루션 테마에 사용되는 에디터 지원페이지 생성
        $this->editorService->createEditablePageForTheme($theme);

        // 하위 Entity 추가
        $theme->editablePages->each(function($ep){
            $ep->editablePageLayout;
        });

        return response()->json($theme, 201);
    }




    /**
     * @throws QpickHttpException
     */
    protected function createTheme(StoreRequest $request)
    {
        $themeProduct = ThemeProduct::findOrFail($request->route('theme_product_id'));

        // 이미 존재 하는지 여부 체크
        $existsTheme = Theme::where([
            'solution_id' => $request->input('solution_id'),
            'theme_product_id' => $request->route('theme_product_id')
        ])->exists();

        if ($existsTheme) {
            throw new QpickHttpException(422, 'theme.disable.already_exists');
        }

        // check create policy
        if (!Auth::user()->can('update', $themeProduct)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        return Theme::create(
            array_merge(
                $request->all(),
                [
                    'theme_product_id' => $request->route('theme_product_id'),
                    'solution_id' => $request->input('solution_id')
                ]
            )
        );
    }


}
