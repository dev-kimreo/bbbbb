<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Themes\Products\Information\IndexRequest;
use App\Http\Requests\Themes\Products\Information\StoreRequest;
use App\Http\Requests\Themes\Products\Information\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Themes\ThemeProductInformation;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ThemeProductInformationController extends Controller
{
    public string $exceptionEntity = "themeProductInformation";

    public function __construct()
    {
    }


    /**
     * @OA\Get (
     *      path="/v1/theme-product/{theme_product_id}/information",
     *      summary="테마 상품 전시정보 목록",
     *      description="테마 상품 전시정보 목록",
     *      operationId="themeProductInformationIndex",
     *      tags={"테마 상품 전시정보"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ThemeProductInformation")
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
     * @param int $themeProductId
     * @return Collection
     */
    public function index(IndexRequest $request, int $themeProductId): Collection
    {
        return collect(ThemeProductInformation::query()->where('theme_product_id', $themeProductId)->get());
    }

    /**
     * @OA\Get (
     *      path="/v1/theme-product/{theme_product_id}/information/{information_id}",
     *      summary="테마 상품 전시정보 상세",
     *      description="테마 상품 전시정보 상세정보",
     *      operationId="themeProductInformationShow",
     *      tags={"테마 상품 전시정보"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ThemeProductInformation")
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
     * @param int $themeProductId
     * @param int $informationId
     * @return Collection
     */
    public function show(int $themeProductId, int $informationId): Collection
    {
        return collect(ThemeProductInformation::findOrFail($informationId));
    }

    /**
     * @OA\Post (
     *      path="/v1/theme-product/{theme_product_id}/information",
     *      summary="테마 상품 전시정보 등록",
     *      description="테마 상품의 새로운 전시정보를 등록합니다.",
     *      operationId="themeProductInformationCreate",
     *      tags={"테마 상품 전시정보"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="description", ref="#/components/schemas/ThemeProductInformation/properties/description")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ThemeProductInformation")
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
     * @param int $themeProductId
     * @return Collection
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, int $themeProductId): Collection
    {
        // exists check
        if (ThemeProductInformation::where('theme_product_id', $themeProductId)->exists()) {
            throw new QpickHttpException(422, 'common.already_exists');
        }

        $information = ThemeProductInformation::create(array_merge(
            $request->all(),
            [
                'theme_product_id' => $themeProductId
            ]
        ));

        $information->refresh();

        return collect($information);
    }

    /**
     * @OA\Patch (
     *      path="/v1/theme-product/{theme_product_id}/information/{information_id}",
     *      summary="테마 상품 전시정보 수정",
     *      description="테마 상품의 전시정보를 수정합니다.",
     *      operationId="themeProductInformationUpdate",
     *      tags={"테마 상품 전시정보"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="description", ref="#/components/schemas/ThemeProductInformation/properties/description"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ThemeProductInformation")
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
     * @param UpdateRequest $request
     * @param int $themeProductId
     * @param int $informationId
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $themeProductId, int $informationId): JsonResponse
    {
        $information = ThemeProductInformation::findOrFail($informationId);
        $information->update($request->all());

        $information->refresh();

        return response()->json(collect($information), 201);
    }


    /**
     * @OA\Delete (
     *      path="/v1/theme-product/{theme_product_id}/information/{information_id}",
     *      summary="테마 상품 전시정보 삭제",
     *      description="테마 상품의 전시정보를 삭제합니다",
     *      operationId="themeProductInformationDestroy",
     *      tags={"테마 상품 전시정보"},
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
     * @param int $themeProductId
     * @param int $informationId
     * @return Response
     */
    public function destroy(int $themeProductId, int $informationId): Response
    {
        $information = ThemeProductInformation::findOrFail($informationId);
        $information->delete();

        return response()->noContent();
    }

}
