<?php

namespace App\Http\Middleware;

use App;
use App\Exceptions\QpickHttpException;
use App\Libraries\StringLibrary;
use Closure;
use Config;
use Illuminate\Http\Request;

class LanguageSwitcher
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @param Closure $next
     * @return string|null
     * @throws QpickHttpException
     */
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->lang ?? Config::get('app.locale');

        if (!StringLibrary::chkIso639_1Code($lang)) {
            throw new QpickHttpException(422, 'common.wrong_language_code', 'lang');
        }

        App::setLocale($lang);

        return $next($request);
    }
}
