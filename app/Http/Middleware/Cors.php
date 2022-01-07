<?php
namespace App\Http\Middleware;
use Closure;
class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // return $next($request);
        $res = $next($request);
        $res->headers->set("Access-Control-Allow-Origin", "*");
        $res->headers->set("Access-Control-Allow-Methods", "GET, POST, DELETE, PATCH, OPTIONS");
        $res->headers->set("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, X-Token-Auth, Authorization, qpick-current-location");

        return $res;
    }
}
