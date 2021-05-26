<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AccessTokenController;


use App\Http\Controllers\BoardController;
use App\Http\Controllers\Boards\OptionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\AttachController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InquiryAnswerController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\ManagerController;


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

Route::patch('/home', function () {
})->name('home');

Route::get('/aaa', [MemberController::class, 'test']);

Route::group([
    'prefix' => 'v1',
    'middleware' => 'language'
], function () {

    /**
     * 회원 관련
     */
    Route::group([
        'prefix' => 'user'
    ], function () {

        // 회원 관련 CRUD
        Route::post('', [MemberController::class, 'store']);

        // 회원 세션 CRUD
        Route::post('/auth', [AccessTokenController::class, 'store']);


        // 인증 필요
        Route::group([
            'middleware' => ['auth:api'],
        ], function () {
            // 회원 관련 CRUD
            Route::patch('/{id}', [MemberController::class, 'update']);

            // 회원 세션 CRUD
            Route::get('/auth', [AccessTokenController::class, 'show']);
            Route::delete('/auth', [AccessTokenController::class, 'destroy']);


            Route::group([
                'middleware' => ['admin'],
            ], function(){
                // 회원 관련 CRUD
                Route::get('', [MemberController::class, 'index']);
                Route::get('/{id}', [MemberController::class, 'show'])->where(['id' => '[0-9]+']);
            });



        });


        // 이메일 재 인증 관련
        Route::group([
            'prefix' => 'email-verification'
        ], function () {
            // 이메일 인증 route
            Route::get('/{verifyKey}/{id}', [MemberController::class, 'verification'])->name('verification.verify');

            // 인증 필요
            Route::group([
                'middleware' => 'auth:api',
            ], function () {
                // 이메일 인증 재발송
                Route::post('', [MemberController::class, 'resendVerificationEmail']);
            });
        });


        /**
         * 비밀번호 관련
         */
        Route::group([
            'prefix' => 'password',
        ], function () {

            /**
             * 비밀번호 찾기 프로세스
             */
            Route::group([
                'prefix' => 'reset-mail'
            ], function () {
                // 비밀번호 변경 링크 유효성 체크
                Route::get('', [MemberController::class, 'changePwdVerification']);

                // 비밀번호 변경 링크 발송
                Route::post('', [MemberController::class, 'passwordResetSendLink']);

                // 비밀번호 변경 링크 발송 후 변경
                Route::patch('', [MemberController::class, 'passwordReset']);
            });

            // 인증 필요
            Route::group([
                'middleware' => 'auth:api',
            ], function () {
                // 비밀번호 검증
                Route::post('', [MemberController::class, 'checkPassword']);

                // 비밀번호 변경
                Route::patch('', [MemberController::class, 'modifyPassword']);
            });

        });


    });


    // 메일
    Route::group([
        'prefix' => 'email'
    ], function ($router) {
    });


    // 관리자 및 권한 관련
    Route::group([
            'middleware' => 'auth:api',
    ], function () {
        Route::resource('authority', AuthorityController::class);
        Route::resource('manager', ManagerController::class,[
            'only' => ['index', 'show', 'store', 'destroy']
        ]);
    });

    // 게시판 관련
    Route::group([
        'prefix' => 'board'
    ], function () {
        // 게시판 CRUD
        Route::get('', [BoardController::class, 'index']);
        Route::get('/{id}', [BoardController::class, 'show'])->where(['id' => '[0-9]+']);

        // 게시글 CRUD
        Route::get('/{boardId}/post', [PostController::class, 'index']);
        Route::get('/{boardId}/post/{id}', [PostController::class, 'show']);

        // 댓글 CRUD
        Route::get('/{boardId}/post/{postId}/reply', [ReplyController::class, 'index']);


        // 인증 필요
        Route::group([
            'middleware' => 'auth:api',
        ], function () {
            // 게시판의 게시글 수를 포함한 목록
            Route::get('/posts-count', [BoardController::class, 'getPostsCount']);


            // 게시판 CRUD
            Route::post('', [BoardController::class, 'store']);
            Route::patch('/{id}', [BoardController::class, 'update']);
            Route::delete('/{id}', [BoardController::class, 'destroy']);

            /**
             * 게시글 CRUD
             */
            Route::post('/{boardId}/post', [PostController::class, 'store']);
            Route::patch('/{boardId}/post/{id}', [PostController::class, 'update']);
            Route::delete('/{boardId}/post/{id}', [PostController::class, 'destroy']);

            // 댓글 CRUD
            Route::post('/{boardId}/post/{postId}/reply', [ReplyController::class, 'store']);
            Route::patch('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'update']);
            Route::delete('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'destroy']);
        });

    });

    // 게시판 옵션 관련
    Route::resource('boardOption', OptionController::class, [
        'only' => ['index']
    ]);


    // 1:1 문의
    Route::group([
        'prefix' => 'inquiry',
        'middleware' => 'auth:api',
    ], function () {

        // 문의 목록
        Route::get('', [InquiryController::class, 'index']);

        // 문의 상세정보
        Route::get('/{inquiryId}', [InquiryController::class, 'show']);

        // 문의 작성
        Route::post('', [InquiryController::class, 'store']);

        // 문의 수정
        Route::patch('{inquiryId}', [InquiryController::class, 'update']);

        // 문의 삭제
        Route::delete('{inquiryId}', [InquiryController::class, 'destroy']);

        // 답변 상세정보 및 작성
        Route::resource('{inquiryId}/answer', InquiryAnswerController::class, [
            'only' => ['store']
        ]);

        // 답변 상세정보 (Customized Router)
        Route::get('{inquiryId}/answer', [InquiryAnswerController::class, 'show']);

        // 답변 수정 (Customized Router)
        Route::patch('{inquiryId}/answer', [InquiryAnswerController::class, 'update']);

        // 답변 삭제 (Customized Router)
        Route::delete('{inquiryId}/answer', [InquiryAnswerController::class, 'destroy']);
    });


    // 게시판 글
    Route::group([
        'prefix' => 'post'
    ], function () {

        // Backoffice
        // 인증 필요
        Route::group([
            'middleware' => ['auth:api', 'admin'],
        ], function () {
            Route::get('', [PostController::class, 'getList']);
        });


    });


    // 첨부파일
    Route::group([
        'prefix' => 'attach',
        'middleware' => 'auth:api'
    ], function () {
        // 임시 파일 첨부
        Route::post('', [AttachController::class, 'store']);

        // 파일 삭제
        Route::delete('/{id}', [AttachController::class, 'delete']);

        // 파일 이동
        Route::patch('/{id}', [AttachController::class, 'update']);

    });


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
    'prefix' => 'backoffice',
    'middleware' => ['auth:api', 'admin']
], function ($router) {

    // 게시글 관련
    Route::group([
        'prefix' => 'post'
    ], function(){
    });


    // 게시판 관련
    Route::group([
        'prefix' => 'board'
    ], function () {
        //

        Route::get('/reInitOption', [BoardController::class, 'reInitBoardOption']);

    });


});





