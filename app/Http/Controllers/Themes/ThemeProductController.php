<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Themes\Products\IndexRequest;
use App\Http\Requests\Themes\Products\StoreRequest;
use App\Http\Requests\Themes\Products\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Themes\ThemeProduct;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ThemeProductController extends Controller
{
    protected ThemeProduct $themeProduct;
    public string $exceptionEntity = "themeProduct";

    public function __construct(ThemeProduct $themeProduct)
    {
        $this->themeProduct = $themeProduct;
    }

    /**
     * @OA\Get (
     *      path="/v1/theme-product",
     *      summary="테마 상품 목록",
     *      description="테마 상품 목록",
     *      operationId="themeProductIndex",
     *      tags={"테마 상품"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ThemeProduct")
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
        $themeProduct = $this->themeProduct->where(['user_partner_id' => Auth::user()->partner->id]);

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($themeProduct) {
                $themeProduct->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $themeProduct->count(), $request->input('per_page'));

        // get data
        return $themeProduct->skip($pagination['skip'])->take($pagination['perPage'])->get();
    }


    /**
     * @OA\Get (
     *      path="/v1/theme-product/{theme_product_id}",
     *      summary="테마 상품 상세",
     *      description="테마 상품 상세정보",
     *      operationId="themeProductShow",
     *      tags={"테마 상품"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ThemeProduct")
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
    public function show($theme_product_id)
    {
        return $this->themeProduct->findOrFail($theme_product_id);
    }


    /**
     * @OA\Post (
     *      path="/v1/theme-product",
     *      summary="테마 상품 등록",
     *      description="새로운 테마 상품을 등록합니다.",
     *      operationId="themeProductCreate",
     *      tags={"테마 상품"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", ref="#/components/schemas/ThemeProduct/properties/name")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ThemeProduct")
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
        // check update policy
        if (!Auth::user()->can('create', $this->themeProduct)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $theme = $this->themeProduct::create(
            array_merge(
                $request->all(),
                [
                    'user_partner_id' => Auth::user()->partner->id
                ]
            )
        );

        $theme->refresh();

        return response()->json(collect($theme), 201);
    }

    /**
     * @OA\Patch (
     *      path="/v1/theme-product/{theme_product_id}",
     *      summary="테마 상품 수정",
     *      description="테마 상품을 수정합니다.",
     *      operationId="themeProductUpdate",
     *      tags={"테마 상품"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/ThemeProduct/properties/name"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ThemeProduct")
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
    public function update(UpdateRequest $request, $theme_product_id): JsonResponse
    {
        $this->themeProduct = $this->themeProduct->findOrFail($theme_product_id);

        // check update policy
        if (!Auth::user()->can('update', $this->themeProduct)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $this->themeProduct->update($request->all());

        return response()->json(collect($this->themeProduct), 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/theme-product/{theme_product_id}",
     *      summary="테마 상품 삭제",
     *      description="테마 상품을 삭제합니다",
     *      operationId="themeProductDestroy",
     *      tags={"테마 상품"},
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
    public function destroy($theme_product_id): Response
    {
        $this->themeProduct = $this->themeProduct->findOrFail($theme_product_id);

        // check delete policy
        if (!Auth::user()->can('delete', $this->themeProduct)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $this->themeProduct->delete();

        return response()->noContent();
    }
}
