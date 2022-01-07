<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Models\Themes\Theme;
use App\Models\Users\UserSolution;
use App\Services\ThemeBuilders\ThemeBuilderInterface;
use App\Services\ThemeBuilders\ThemeCafe24BuilderService;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ThemeBuildController extends Controller
{
    public string $exceptionEntity = "themeBuild";

    /**
     * @param int $theme_id
     * @throws QpickHttpException
     */
    public function build(int $theme_id)
    {
        // 테마 가져오기
        $theme = Theme::query()->findOrFail($theme_id);

        // 권한 검사
        if (Auth::id() != $theme->product->user_partner_id) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 빌더
        $builder = $this->getBuilder($theme);

        // 다운로드
        $builder->build($theme_id);
        $builder->download();
    }

    /**
     * @param Request $request
     * @param int $theme_id
     * @return Collection
     * @throws QpickHttpException
     */
    public function export(Request $request, int $theme_id): Collection
    {
        // 데이터 가져오기
        $theme = Theme::findOrFail($theme_id);
        $solution = UserSolution::query()->findOrFail($request->input('user_solution_id'));
        $solutionId = $solution->solution_user_id;

        // 권한 검사
        if (Auth::id() != $theme->product->user_partner_id || Auth::id() != $solution->user_id) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        if ($solution->solution_id != $theme->solution->id) {
            throw new QpickHttpException(422, 'theme.solution.not_matched');
        }

        // 빌더 및 FTP 정보
        $builder = $this->getBuilder($theme);
        list($host, $port) = $this->getFtpInfo($request, $theme, $solutionId);

        // 업로드
        $builder->build($theme_id);
        $builder->ftpUpload($host, $port, $solutionId, $request->input('password') ?? '', '/sde_design/skin1');

        // 결과
        return collect(['res' => true]);
    }

    /**
     * @throws QpickHttpException
     */
    protected function getBuilder(Theme $theme): ThemeBuilderInterface
    {
        switch ($theme->solution->name) {
            case '카페24':
                $builder = new ThemeCafe24BuilderService();
                break;
            default:
                throw new QpickHttpException(422, 'theme.solution.not_found');
        }

        return $builder;
    }

    /**
     * @throws QpickHttpException
     */
    protected function getFtpInfo(Request $request, Theme $theme, string $solutionId): array
    {
        switch ($theme->solution->name) {
            case '카페24':
                $host = $request->input('host') ?? $solutionId . '.cafe24.com';
                $port = $request->input('port') ?? 21;
                break;
            default:
                throw new QpickHttpException(422, 'theme.solution.not_found');
        }

        return [$host, $port];
    }
}
