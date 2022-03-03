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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Remove the specified resource from storage.
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
