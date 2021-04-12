<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AccessTokenController;


use App\Http\Controllers\BoardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AttachController;



use App\Models\Board;

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

Route::patch('/home', function(){})->name('home');

Route::get('/aaa', [MemberController::class, 'test']);

Route::group([
    'prefix' => 'v1',
    'middleware' => 'language'
], function (){

    /**
     * 회원 관련
     */
    Route::group([
        'prefix' => 'member'
    ], function (){

        // 회원가입
        Route::post('', [MemberController::class, 'register']);

        // 로그인
        Route::post('/auth', [AccessTokenController::class, 'issueToken']);

        // 인증 필요
        Route::group([
            'middleware' => 'auth:api',
        ], function () {
            // 회원정보
            Route::get('', [MemberController::class, 'info']);

            // 회원 정보 수정
            Route::patch('', [MemberController::class, 'modify']);

            // 로그아웃
            Route::delete('/auth', [MemberController::class, 'logout']);
        });


        /**
         * 비밀번호 변경
         */
        Route::group([
            'prefix' => 'password',
            'middleware' => 'auth:api',
        ], function(){
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


    // 회원가입시 인증 메일 발송
    Route::group([
        'prefix' => 'email'
    ], function($router){
        // 이메일 인증 route
        Route::get('/{verifyKey}/{id}', [MemberController::class, 'verification'])->name('verification.verify');

        // 인증 필요
        Route::group([
            'middleware' => 'auth:api',
        ], function () {

            // 이메일 인증 재발송
            Route::post('/verificationResend', [MemberController::class, 'verificationResend']);

        });
    });


    // 게시판 관련
    Route::group([
        'prefix' => 'board'
    ], function(){
        // 게시판 상세 정보
        Route::get('/{id}', [BoardController::class, 'getBoardInfo']);

        // 게시판 목록
        Route::get('', [BoardController::class, 'getList']);



        // 게시글 목록
        Route::get('/{boardNo}/post', [PostController::class, 'getList']);

        // 게시글 상세 정보
        Route::get('/{boardNo}/post/{id}', [PostController::class, 'getInfo']);

        // 인증 필요
        Route::group([
            'middleware' => 'auth:api',
        ], function () {

            // 게시글 작성
            Route::post('/{boardNo}/post', [PostController::class, 'create']);

            // 게시글 수정
            Route::patch('/{boardNo}/post/{id}', [PostController::class, 'modify']);

            // 게시글 삭제
            Route::delete('/{boardNo}/post/{id}', [PostController::class, 'delete']);
        });
    });


    // 게시판 글
    Route::group([
        'prefix' => 'post'
    ], function(){



    });


    // 첨부파일
    Route::group([
        'prefix'    => 'attach',
        'middleware' => 'auth:api'
    ], function(){
        // 임시 파일 첨부
        Route::post('', [AttachController::class, 'create']);

        // 파일 삭제
        Route::delete('/{id}', [AttachController::class, 'delete']);

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


// 관리자
Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth:api', 'admin']
], function ($router){

    // 게시판 관련
    Route::group([
        'prefix' => 'board'
    ], function(){

        Route::get('/reInitOption', [BoardController::class, 'reInitBoardOption']);
//            // 파라미터 bind
//            Route::bind('type', function ($type) {
//                $data = Board::where('type', $type)->firstOrFail();
//                return $data;
//            });

        // 게시판 생성
        Route::post('', [BoardController::class, 'create']);

        // 게시판 정보 수정
        Route::patch('/{id}', [BoardController::class, 'modify']);

        // 게시판 옵션 목록
        Route::get('/options', [BoardController::class, 'getOptionList']);

    });


//        Route::post('/login', [AuthController::class, 'login']);
//        Route::post('/register', [AuthController::class, 'register']);
//        Route::post('/logout', [AuthController::class, 'logout']);
//        Route::get('/user-profile', [AuthController::class, 'userProfile']);

});







//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
