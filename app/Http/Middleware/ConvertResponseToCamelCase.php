<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Libraries\KeyCaseConverterLibrary;

class ConvertResponseToCamelCase
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
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setData(
                resolve(KeyCaseConverterLibrary::class)->convert(
                    KeyCaseConverterLibrary::CASE_CAMEL,
                    json_decode($response->content(), true)
                )
            );
        } else if ($response instanceof Response && $response->getcontent() ) {
            $response->setContent(resolve(KeyCaseConverterLibrary::class)->convert(
                KeyCaseConverterLibrary::CASE_CAMEL,
                json_decode($response->getContent(), true)
            ));
        }

        return $response;
    }
}
