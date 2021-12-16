<?php

namespace App\Http\Controllers\LinkedComponents;

use App\Http\Controllers\Controller;
use App\Services\ComponentRenderingService;
use Illuminate\Http\Request;

class ScriptRequestController extends Controller
{

    /**
     * @OA\Get (
     *      path="/v1/component/script/{hash}.js",
     *      summary="컴포넌트 스크립트 요청",
     *      description="설명: https://www.notion.so/Component-Rendering-d474a80d2291486f83551058c2e9121d#9e0a0deeae044389a7784160531202da",
     *      operationId="ComponentScriptRequest",
     *      tags={"연동 컴포넌트"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="type", type="string", example="module", description="반환할 스크립트 형식<br/>module:모듈방식(기본값), jsonp:JSONP 방식"),
     *              @OA\Property(property="callback", type="string", example="render", description="JSONP 방식 선택시, 콜백함수의 이름")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "partner_auth":{}
     *      }}
     *  )
     */
    public function show(Request $req, string $hash)
    {
        if($req->input('type') == 'jsonp') {
            $code = ComponentRenderingService::getJsonp($hash, $req->input('callback') ?? 'render');
        } else {
            $code = ComponentRenderingService::getScript($hash);
        }

        return response($code, 200)
            ->header('Content-Type', 'application/javascript');
    }
}
