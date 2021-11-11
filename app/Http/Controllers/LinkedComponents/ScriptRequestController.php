<?php

namespace App\Http\Controllers\LinkedComponents;

use App\Http\Controllers\Controller;
use App\Services\ComponentRenderingService;
use Illuminate\Http\Request;

class ScriptRequestController extends Controller
{

    /**
     * @OA\Get (
     *      path="/v1/component/script/{hash}",
     *      summary="컴포넌트 스크립트 요청",
     *      description="설명: https://www.notion.so/Component-Rendering-d474a80d2291486f83551058c2e9121d#9e0a0deeae044389a7784160531202da",
     *      operationId="ComponentScriptRequest",
     *      tags={"연동 컴포넌트"},
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
        return response(ComponentRenderingService::getScript($hash), 200)
            ->header('Content-Type', 'application/javascript');
    }
}
