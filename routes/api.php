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

    // 이메일 인증 route
    Route::get('/verification.verify/{id}', [MemberController::class, 'verification'])->name('verification.verify');

    Route::group([
        'prefix' => 'v1'
    ], function ($router){

        Route::group([
            'prefix' => 'member'
        ], function ($router){
            Route::post('', [MemberController::class, 'register']);
            Route::get('', [MemberController::class, 'info']);
            Route::patch('', [MemberController::class, 'modify']);


            Route::group([
                'prefix' => 'password'
            ], function($router){

                // 비밀번호 검증
                Route::post('', [MemberController::class, 'checkPassword']);

                // 비밀번호 변경
                Route::patch('', [MemberController::class, 'modifyPassword']);
            });



            Route::delete('/auth', [MemberController::class, 'logout']);
        });

        Route::group([
            'prefix' => 'login'
        ], function($router){
            Route::post('', [MemberController::class, 'login']);
        });

        Route::group([
            'prefix' => 'email'
        ], function($router){
            Route::post('/verificationResend', [MemberController::class, 'verificationResend']);
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
