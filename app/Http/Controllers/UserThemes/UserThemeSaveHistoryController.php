<?php

namespace App\Http\Controllers\UserThemes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserThemes\SaveHistory\StoreRequest;
use App\Models\UserThemes\UserTheme;
use App\Models\UserThemes\UserThemeSaveHistory;
use Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class UserThemeSaveHistoryController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/user-theme/{user_theme_id}/save-history",
     *      summary="저장 히스토리 목록",
     *      description="회원테마 저장 히스토리 목록",
     *      operationId="SaveHistoryList",
     *      tags={"회원 테마 저장 히스토리"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, description="페이지" ),
     *              @OA\Property(property="perPage", type="integer", example=15, description="한 페이지에 보여질 수" ),
     *              @OA\Property(property="name", type="string", ref="#/components/schemas/Widget/properties/name" ),
     *              @OA\Property(property="enable", type="boolean", example="1", description="사용구분<br/>1:사용, 0:미사용" ),
     *              @OA\Property(property="onlyForManager", type="boolean", example="0", description="관리자 전용 위젯 여부<br/>1:관리자전용, 0:모든 사용자용" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(ref="#/components/schemas/UserThemeSaveHistory")
     *              )
     *          )
     *      )
     *  )
     *
     * @param int $userThemeId
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(int $userThemeId): Collection
    {
        $this->chkAuth($userThemeId);

        return collect(UserThemeSaveHistory::query()->where('user_theme_id', $userThemeId)->get());
    }

    /**
     * @OA\Get(
     *      path="/v1/user-theme/{user_theme_id}/save-history/{history_id}",
     *      summary="저장 히스토리 상세",
     *      description="회원테마 저장 히스토리 상세",
     *      operationId="SaveHistoryShow",
     *      tags={"회원 테마 저장 히스토리"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/UserThemeSaveHistory")
     *          )
     *      )
     *  )
     *
     * @param int $userThemeId
     * @param int $userThemeSaveHistoryId
     * @return Collection
     * @throws QpickHttpException
     */
    public function show(int $userThemeId, int $userThemeSaveHistoryId): Collection
    {
        $this->chkAuth($userThemeId);
        $res = UserThemeSaveHistory::query()
            ->where('id', $userThemeSaveHistoryId)
            ->where('user_theme_id', $userThemeId)
            ->firstOrFail();

        return collect($res);
    }

    /**
     * @OA\Post(
     *      path="/v1/user-theme/{user_theme_id}/save-history",
     *      summary="저장 히스토리 등록",
     *      description="회원테마 저장 히스토리 등록",
     *      operationId="SaveHistoryCreate",
     *      tags={"회원 테마 저장 히스토리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"data"},
 *                  @OA\Property(property="data", type="JSON", example="{}", description="히스토리 데이터"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/UserThemeSaveHistory")
     *          )
     *      )
     *  )
     *
     * @param StoreRequest $request
     * @param int $userThemeId
     * @return Collection
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, int $userThemeId): Collection
    {
        $this->chkAuth($userThemeId);

        $res = UserThemeSaveHistory::query()->create(
            [
                'user_theme_id' => $userThemeId,
                'data' => json_decode($request->data)
            ]
        );

        return collect(UserThemeSaveHistory::query()->find($res->id));
    }

    /**
     * @OA\Delete(
     *      path="/v1/user-theme/{user_theme_id}/save-history/{history_id}",
     *      summary="저장 히스토리 삭제",
     *      description="회원테마 저장 히스토리 삭제",
     *      operationId="SaveHistoryDelete",
     *      tags={"회원 테마 저장 히스토리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      )
     *  )
     *
     * @param int $userThemeId
     * @param int $userThemeSaveHistoryId
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $userThemeId, int $userThemeSaveHistoryId): Response
    {
        $this->chkAuth($userThemeId);
        UserThemeSaveHistory::query()->findOrFail($userThemeSaveHistoryId)->delete();
        return response()->noContent();
    }

    /**
     * @throws QpickHttpException
     */
    protected function chkAuth($userThemeId)
    {
        $userTheme = UserTheme::query()->findOrFail($userThemeId);

        if (!Auth::isLoggedForBackoffice() && $userTheme->user_id != Auth::id()) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }
    }
}
