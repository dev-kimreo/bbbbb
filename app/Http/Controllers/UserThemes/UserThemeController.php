<?php

namespace App\Http\Controllers\UserThemes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserThemes\IndexRequest;
use App\Http\Requests\UserThemes\StoreRequest;
use App\Http\Requests\UserThemes\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\UserThemes\UserTheme;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class UserThemeController extends Controller
{
    public string $exceptionEntity = "userTheme";

    /**
     * @OA\Get (
     *      path="/v1/user-theme",
     *      summary="회원 테마 목록",
     *      description="회원 테마 목록",
     *      operationId="UserThemeIndex",
     *      tags={"회원 테마"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserTheme")
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
        $userThemeBuilder = UserTheme::query();
        $userThemeBuilder->where('user_id', Auth::user()->getAttribute('id'));

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($userThemeBuilder) {
                $userThemeBuilder->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $userThemeBuilder->count(), $request->input('per_page'));

        // get data
        return collect($userThemeBuilder->skip($pagination['skip'])->take($pagination['perPage'])->get());
    }

    /**
     * @OA\Get (
     *      path="/v1/user-theme/{user_thme_id}",
     *      summary="회원 테마 상세정보",
     *      description="회원 테마 상세정보",
     *      operationId="UserThemeShow",
     *      tags={"회원 테마"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserTheme")
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
     * @param int $userThemeId
     * @return Collection
     */
    public function show(int $userThemeId): Collection
    {
        return collect(UserTheme::where('user_id', Auth::user()->getAttribute('id'))->findOrFail($userThemeId));
    }


    /**
     * @OA\Post (
     *      path="/v1/user-theme",
     *      summary="회원 테마 등록",
     *      description="회원 테마 등록",
     *      operationId="UserThemeStore",
     *      tags={"회원 테마"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"theme_id","name"},
     *              @OA\Property(property="theme_id", ref="#/components/schemas/UserTheme/properties/theme_id"),
     *              @OA\Property(property="name", ref="#/components/schemas/UserTheme/properties/name"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserTheme")
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
        $userTheme = UserTheme::create(array_merge($request->all(), [
            'user_id' => Auth::user()->getAttribute('id')
        ]));

        $userTheme->refresh();

        return response()->json($userTheme, 201);
    }

    /**
     * @OA\Patch (
     *      path="/v1/user-theme/{user_theme_id}",
     *      summary="회원 테마 수정",
     *      description="회원 테마 수정",
     *      operationId="UserThemeUpdate",
     *      tags={"회원 테마"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/UserTheme/properties/name"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserTheme")
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
     * @param int $userThemeId
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $userThemeId): JsonResponse
    {
        $userTheme = UserTheme::where('user_id', Auth::user()->getAttribute('id'))->findOrFail($userThemeId);
        $userTheme->update($request->all());

        return response()->json($userTheme, 201);
    }

    /**
     * @OA\Delete (
     *      path="/v1/user-theme/{user_theme_id}",
     *      summary="회원 테마 삭제",
     *      description="회원 테마 삭제",
     *      operationId="UserThemeDestroy",
     *      tags={"회원 테마"},
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
     * @param int $userThemeId
     * @return Response
     */
    public function destroy(int $userThemeId): Response
    {
        $userTheme = UserTheme::where('user_id', Auth::user()->getAttribute('id'))->findOrFail($userThemeId);
        $userTheme->delete();

        return response()->noContent();
    }


}
