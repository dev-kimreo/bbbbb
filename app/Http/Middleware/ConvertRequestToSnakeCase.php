<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Libraries\KeyCaseConverterLibrary;

class ConvertRequestToSnakeCase
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->originals = $request->all();

        $request->replace(
            resolve(KeyCaseConverterLibrary::class)->convert(
                KeyCaseConverterLibrary::CASE_SNAKE,
                $request->all()
            )
        );

        return $next($request);
    }
}
