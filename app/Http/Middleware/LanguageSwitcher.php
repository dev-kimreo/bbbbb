<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App;
use Config;


class LanguageSwitcher
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle(Request $request, Closure $next)
    {
        if (isset($request->lang) && $request->lang ) {
            App::setLocale($request->lang);
        } else {
            App::setLocale(Config::get('app.locale'));
        }

        return $next($request);
    }
}
