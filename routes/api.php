<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
], function ($router) {

    Route::group([
        'prefix' => 'v1'
    ], function ($router){

        Route::group([
            'prefix' => 'member'
        ], function ($router){
            Route::post('', [MemberController::class, 'register']);
            Route::get('/user-profile', [MemberController::class, 'userProfile']);
        });

//        Route::post('/logout', [AuthController::class, 'logout']);

    });

//    Route::group([
//        'prefix' => 'v2'
//    ], function ($router){
//
//        Route::get('/user-profile', [AuthController::class, 'userProfile']);
//
//    });
});

// 관리자
/*Route::group([
    'middleware' => 'api',
], function ($router) {

    Route::group([
        'prefix' => 'admin'
    ], function ($router){

//        Route::post('/login', [AuthController::class, 'login']);
//        Route::post('/register', [AuthController::class, 'register']);
//        Route::post('/logout', [AuthController::class, 'logout']);
//        Route::get('/user-profile', [AuthController::class, 'userProfile']);

    });

});*/






//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
