<?php

use App\Http\Controllers\UserAdvAgreeController;
use App\Http\Controllers\UserLinkedSolutionController;
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
    Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
        // 회원 관련 CRUD
        Route::post('', [MemberController::class, 'store'])->withoutmiddleware('auth:api');
        Route::get('', [MemberController::class, 'index'])->middleware('admin');
        Route::get('/{id}', [MemberController::class, 'show'])->where(['id' => '[0-9]+']);
        Route::patch('/{id}', [MemberController::class, 'update'])->where(['id' => '[0-9]+']);
        Route::delete('/{id}', [MemberController::class, 'destroy'])->where(['id' => '[0-9]+']);

        // 회원 세션 CRUD
        Route::post('/auth', [AccessTokenController::class, 'store'])->withoutmiddleware('auth:api');
        Route::get('/auth', [AccessTokenController::class, 'show']);
        Route::delete('/auth', [AccessTokenController::class, 'destroy']);

        // 회원 연동 솔루션 CD (추가 및 삭제)
        Route::resource('/{user_id}/linkedSolution', UserLinkedSolutionController::class, [
            'only' => ['store', 'destroy']
        ]);

        // 광고성정보 수신동의 여부 CD (동의 및 거부)
        Route::patch('/{user_id}/advAgree', [UserAdvAgreeController::class, 'update']);
    });

    /**
     * 회원 / 이메일 인증
     */
    Route::group(['prefix' => 'user/email-verification'], function () {
        // 이메일 인증링크 재발송 & 인증링크 클릭시 랜딩 페이지
        Route::post('', [MemberController::class, 'resendVerificationEmail'])->middleware('auth:api');
        Route::get('/{verifyKey}/{id}', [MemberController::class, 'verification'])->name('verification.verify');
    });

    /**
     * 회원 / 비밀번호 관련
     */
    Route::group(['prefix' => 'user/password'], function () {
        // 비밀번호 변경 링크 발송 & 유효성 체크 & 발송 후 변경
        Route::post('/reset-mail', [MemberController::class, 'passwordResetSendLink']);
        Route::get('/reset-mail', [MemberController::class, 'changePwdVerification']);
        Route::patch('/reset-mail', [MemberController::class, 'passwordReset']);

        // 비밀번호 검증 & 비밀번호 변경
        Route::post('', [MemberController::class, 'checkPassword'])->middleware('auth:api');
        Route::patch('', [MemberController::class, 'modifyPassword'])->middleware('auth:api');
    });

    /**
     * 관리자 및 권한 관련
     */
    Route::group(['middleware' => 'auth:api'], function () {
        Route::resource('authority', AuthorityController::class);
        Route::resource('manager', ManagerController::class,[
            'only' => ['index', 'show', 'store', 'destroy']
        ]);
    });

    /**
     * 게시판 관련
     */
    Route::group(['prefix' => 'board', 'middleware' => 'auth:api'], function () {
        // 게시판 CRUD
        Route::post('', [BoardController::class, 'store']);
        Route::get('', [BoardController::class, 'index'])->withoutmiddleware('auth:api');
        Route::get('/{id}', [BoardController::class, 'show'])->withoutmiddleware('auth:api')->where(['id' => '[0-9]+']);
        Route::patch('/{id}', [BoardController::class, 'update']);
        Route::delete('/{id}', [BoardController::class, 'destroy']);

        // 게시글 CRUD
        Route::post('/{boardId}/post', [PostController::class, 'store']);
        Route::get('/{boardId}/post', [PostController::class, 'index'])->withoutmiddleware('auth:api');
        Route::get('/{boardId}/post/{id}', [PostController::class, 'show'])->withoutmiddleware('auth:api');
        Route::patch('/{boardId}/post/{id}', [PostController::class, 'update']);
        Route::delete('/{boardId}/post/{id}', [PostController::class, 'destroy']);

        // 댓글 CRUD
        Route::post('/{boardId}/post/{postId}/reply', [ReplyController::class, 'store']);
        Route::get('/{boardId}/post/{postId}/reply', [ReplyController::class, 'index'])->withoutmiddleware('auth:api');
        Route::patch('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'update']);
        Route::delete('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'destroy']);

        // Unique API
        // 게시판의 게시글 수를 포함한 목록
        Route::get('/posts-count', [BoardController::class, 'getPostsCount']);

        // 게시판의 전시순서 변경
        Route::patch('/{id}/sort', [BoardController::class, 'updateBoardSort']);
    });

    // 게시판 옵션 관련
    Route::resource('boardOption', OptionController::class, [
        'only' => ['index']
    ]);

    /**
     * 1:1 문의
     */
    Route::group(['prefix' => 'inquiry', 'middleware' => 'auth:api'], function () {
        // 문의 CRUD
        Route::post('', [InquiryController::class, 'store']);
        Route::get('', [InquiryController::class, 'index']);
        Route::get('/{inquiryId}', [InquiryController::class, 'show']);
        Route::patch('{inquiryId}', [InquiryController::class, 'update']);
        Route::delete('{inquiryId}', [InquiryController::class, 'destroy']);

        // 답변 CRUD (Customized Router)
        Route::post('{inquiryId}/answer', [InquiryAnswerController::class, 'store']);
        Route::get('{inquiryId}/answer', [InquiryAnswerController::class, 'show']);
        Route::patch('{inquiryId}/answer', [InquiryAnswerController::class, 'update']);
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





