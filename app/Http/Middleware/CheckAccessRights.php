<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Auth\AuthenticationException;
use App\Exceptions\QpickHttpException;
use Illuminate\Http\Request;

class CheckAccessRights
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws AuthenticationException
     * @throws QpickHttpException
     */
    public function handle(Request $request, Closure $next, ...$range)
    {
        if(!in_array('guest', $range) && !Auth::check()) {
            throw new AuthenticationException();
        }

        if (!(
            (in_array('guest', $range) && !Auth::check())
            || (in_array('associate', $range) && Auth::isLoggedForFront())
            || (in_array('regular', $range) && Auth::hasAccessRightsToFrontForRegular())
            || (in_array('owner', $range) && Auth::isSameUserAs($request->route('user_id')))
            || (in_array('backoffice', $range) && Auth::hasAccessRightsToBackoffice())
        )) {
            throw new QpickHttpException(403, 'common.forbidden');
        }

        return $next($request);
    }
}
