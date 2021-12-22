<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Models\Themes\Theme;
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
            //throw new QpickHttpException(403, 'common.unauthorized');
            // TODO: 테스트 후 권한검사 원상복귀
        }

        // 빌더
        switch($theme->solution->name) {
            case '카페24':
                $builder = new ThemeCafe24BuilderService();
                break;
            default:
                throw new QpickHttpException(422, 'something.here');
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
        // 테마 가져오기
        $theme = Theme::query()->findOrFail($theme_id);

        // 권한 검사
        if (Auth::id() != $theme->product->user_partner_id) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 빌더
        switch ($theme->solution->name) {
            case '카페24':
                $builder = new ThemeCafe24BuilderService();
                $host = 'raphanus.cafe24.com';
                $port = 21;
                break;
            default:
                throw new QpickHttpException(422, 'something.here');
        }

        // FTP 업로드
        $builder->build($theme_id);
        $builder->ftpUpload($host, $port, 'raphanus', $request->input('password'), '/sde_design/skin1');

        // 결과
        return collect(['res' => true]);
    }
}
