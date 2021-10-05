<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Themes\Products\IndexRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Themes\ThemeProduct;
use Auth;

class ThemeProductController extends Controller
{
    protected ThemeProduct $themeProduct;

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


    public function store()
    {
    }

    public function update()
    {
    }

    public function destroy()
    {
    }
}
