<?php

namespace App\Http\Controllers\UserThemes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserThemes\PurchaseHistories\IndexRequest;
use App\Http\Requests\UserThemes\PurchaseHistories\StoreRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\UserThemes\UserThemePurchaseHistory;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class UserThemePurchaseHistoryController extends Controller
{
    public string $exceptionEntity = "userThemePurchaseHistory";

    /**
     * @OA\Get (
     *      path="/v1/user-theme-purchase-history",
     *      summary="회원 테마 구매내역 목록",
     *      description="회원 테마 구매내역 목록",
     *      operationId="UserThemePurchaseHistoryIndex",
     *      tags={"회원 테마 구매내역"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserThemePurchaseHistory")
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
    public function index(IndexRequest $request): Collection
    {
        $purchaseHistoryBuilder = UserThemePurchaseHistory::query();
        $purchaseHistoryBuilder->where('user_id', Auth::user()->getAttribute('id'));

        if ($i = $request->input('theme_id')) {
            $purchaseHistoryBuilder->where('theme_id', $i);
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($purchaseHistoryBuilder) {
                $purchaseHistoryBuilder->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $purchaseHistoryBuilder->count(), $request->input('per_page'));

        // get data
        return collect($purchaseHistoryBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get());
    }

    /**
     * @OA\Get (
     *      path="/v1/user-theme-purchase-history/{user_theme_purchase_history_id}",
     *      summary="회원 테마 구매내역 상세정보",
     *      description="회원 테마 구매내역 상세정보",
     *      operationId="UserThemePurchaseHistoryShow",
     *      tags={"회원 테마 구매내역"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserThemePurchaseHistory")
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
     * @param int $purchaseHistoryId
     * @return Collection
     */
    public function show(int $purchaseHistoryId): Collection
    {
        return collect(UserThemePurchaseHistory::where('user_id', Auth::user()->getAttribute('id'))->findOrFail($purchaseHistoryId));
    }


    /**
     * @OA\Post (
     *      path="/v1/user-theme-purchase-history",
     *      summary="회원 테마 구매내역 등록",
     *      description="회원 테마 구매내역 등록",
     *      operationId="UserThemePurchaseHistoryStore",
     *      tags={"회원 테마 구매내역"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"theme_id"},
     *              @OA\Property(property="theme_id", ref="#/components/schemas/UserThemePurchaseHistory/properties/theme_id"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserThemePurchaseHistory")
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
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $purchaseHistory = UserThemePurchaseHistory::create(array_merge($request->all(), [
            'user_id' => Auth::user()->getAttribute('id')
        ]));

        $purchaseHistory->refresh();

        return response()->json($purchaseHistory, 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/user-theme-purchase-history/{user_theme_purchase_history_id}",
     *      summary="회원 테마 구매내역 삭제",
     *      description="회원 테마 구매내역 삭제",
     *      operationId="UserThemePurchaseHistoryDestroy",
     *      tags={"회원 테마 구매내역"},
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
     * @param int $purchaseHistoryId
     * @return Response
     */
    public function destroy(int $purchaseHistoryId): Response
    {
        $userTheme = UserThemePurchaseHistory::where('user_id', Auth::user()->getAttribute('id'))->findOrFail($purchaseHistoryId);
        $userTheme->delete();

        return response()->noContent();
    }


}
