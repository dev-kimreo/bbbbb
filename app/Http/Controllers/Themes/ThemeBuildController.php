<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Models\Themes\Theme;
use App\Models\Users\UserSite;
use App\Services\ThemeBuilders\ThemeCafe24BuilderService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Flysystem\FileNotFoundException;

class ThemeBuildController extends Controller
{
    /**
     * @param Request $request
     * @param int $theme_id
     * @throws QpickHttpException
     */
    public function build(Request $request, int $theme_id)
    {
        // 테마 가져오기
        $theme = Theme::query()->findOrFail($theme_id);

        // 권한 검사
        if (Auth::id() != $theme->product->user_partner_id) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 빌더
        switch($theme->solution->name) {
            case '카페24':
                $builder = new ThemeCafe24BuilderService();
                break;
            default:
                throw new QpickHttpException(422, 'theme.solution.not_found');
        }

        // 다운로드
        $builder->build($theme_id);
        $builder->download();
    }

    /**
     * @param Request $request
     * @param int $theme_id
     * @return Collection
     * @throws FileNotFoundException
     * @throws QpickHttpException
     */
    public function export(Request $request, int $theme_id): Collection
    {
        // 데이터 가져오기
        $theme = Theme::query()->findOrFail($theme_id);
        $site = UserSite::query()->findOrFail($request->input('user_site_id'));
        $solutionId = $site->solution_user_id;

        // 권한 검사
        if (Auth::id() != $theme->product->user_partner_id || Auth::id() != $site->user_id) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        if ($site->solution_id != $theme->solution->id) {
            throw new QpickHttpException(422, 'theme.solution.not_matched');
        }

        // 빌더
        switch ($theme->solution->name) {
            case '카페24':
                $builder = new ThemeCafe24BuilderService();
                $host = $solutionId . '.cafe24.com';
                $port = 21;
                break;
            default:
                throw new QpickHttpException(422, 'theme.solution.not_found');
        }

        // FTP 업로드
        $builder->build($theme_id);
        $builder->ftpUpload($host, $port, $solutionId, $request->input('password') ?? '', '/sde_design/skin1');

        // 결과
        return collect(['res' => true]);
    }
}
