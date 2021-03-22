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


//    // 이메일 인증 route
//    Route::get('/{verifyKey}/{id}', [MemberController::class, 'changePwdVerification'])->name('davinci.verification');


    Route::group([
        'prefix' => 'v1'
    ], function ($router){

        /**
         * 회원 관련
         */
        Route::group([
            'prefix' => 'member'
        ], function ($router){
            // 회원가입
            Route::post('', [MemberController::class, 'register']);

            // 회원정보
            Route::get('', [MemberController::class, 'info']);

            // 회원 정보 수정
            Route::patch('', [MemberController::class, 'modify']);

            // 로그아웃
            Route::delete('/auth', [MemberController::class, 'logout']);

            /**
             * 비밀번호 변경
             */
            Route::group([
                'prefix' => 'password'
            ], function($router){

                // 비밀번호 검증
                Route::post('', [MemberController::class, 'checkPassword']);

                // 비밀번호 변경
                Route::patch('', [MemberController::class, 'modifyPassword']);
            });

            /**
             * 비밀번호 찾기 프로세스
             */
            // 비밀번호 변경 링크 발송
            Route::post('/passwordResetSendLink', [MemberController::class, 'passwordResetSendLink']);

            // 비밀번호 변경 링크 유효성 체크
            Route::post('/checkChangePwdAuth', [MemberController::class, 'changePwdVerification']);

            // 비밀번호 변경 링크 발송 후 변경
            Route::patch('/passwordReset', [MemberController::class, 'passwordReset']);


        });

        Route::group([
            'prefix' => 'login'
        ], function($router){
            Route::post('', [MemberController::class, 'login']);
        });

        // 회원가입시 인증 메일 발송
        Route::group([
            'prefix' => 'email'
        ], function($router){
            // 이메일 인증 route
            Route::get('/{verifyKey}/{id}', [MemberController::class, 'verification'])->name('verification.verify');

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
