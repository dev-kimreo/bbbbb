<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TestController extends Controller
{
    public function test(): array
    {
        return ['referer' => request()->headers->get('referer'), 'request' => request()->all()];
    }
}
