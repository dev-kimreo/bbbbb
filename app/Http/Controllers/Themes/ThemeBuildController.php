<?php

namespace App\Http\Controllers\Themes;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Models\Themes\Theme;
use App\Services\ThemeBuilders\ThemeCafe24BuilderService;
use Auth;
use Illuminate\Http\Request;

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
        if(Auth::id() != $theme->product->user_partner_id)
        {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // 빌더
        switch($theme->solution->name)
        {
            case '카페24': $builder = new ThemeCafe24BuilderService(); break;
            default:
                throw new QpickHttpException(422, 'something.here');
        }

        // 다운로드
        $builder->download($theme_id);
    }
}
