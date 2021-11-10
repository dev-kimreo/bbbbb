<?php

namespace App\Http\Controllers\LinkedComponents;

use App\Http\Controllers\Controller;
use App\Services\ComponentRenderingService;
use Illuminate\Http\Request;

class ScriptRequestController extends Controller
{
    public function show(Request $req, string $hash)
    {
        return response(
            ComponentRenderingService::getScript($req->input('func_name'), $hash),
            200
        )->header('Content-Type', 'application/javascript');
    }
}
