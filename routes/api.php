<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\AttachController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\Boards\OptionController;
use App\Http\Controllers\InquiryAnswerController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\Users\ManagerController;
use App\Http\Controllers\Users\UserAdvAgreeController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserLinkedSolutionController;
use Illuminate\Support\Facades\Route;


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

Route::group([
    'prefix' => 'v1',
    'middleware' => [
        'language',
        'requestToSnake',
        'responseToCamel',
    ]
], function () {
    /**
     * 회원 관련
     */
    Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
        // 회원 관련 CRUD
        Route::post('', [UserController::class, 'store'])->withoutmiddleware('auth:api');
        Route::get('', [UserController::class, 'index'])->middleware('chkAccess:backoffice');
        Route::group(['middleware' => 'chkAccess:owner,backoffice'], function () {
            Route::get('/{user_id}', [UserController::class, 'show'])->where(['user_id' => '[0-9]+']);
            Route::patch('/{user_id}', [UserController::class, 'update'])->where(['user_id' => '[0-9]+']);
            Route::delete('/{user_id}', [UserController::class, 'destroy'])->where(['user_id' => '[0-9]+']);
        });

        // 회원 세션 CRUD
        Route::post('/auth', [AccessTokenController::class, 'store'])->withoutmiddleware('auth:api');
        Route::get('/auth', [AccessTokenController::class, 'show']);
        Route::delete('/auth', [AccessTokenController::class, 'destroy']);

        // 회원 연동 솔루션 CD (추가 및 삭제)
        Route::resource('/{user_id}/linkedSolution', UserLinkedSolutionController::class, [
            'only' => ['store', 'destroy']
        ])->middleware('chkAccess:owner,backoffice');

        // 광고성정보 수신동의 여부 CD (동의 및 거부)
        Route::patch('/{user_id}/advAgree', [UserAdvAgreeController::class, 'update'])
            ->middleware('chkAccess:owner,backoffice');

        // 관리자 Super Login
        Route::post('/{user_id}/auth', [UserController::class, 'personalClientLogin'])
            ->middleware('chkAccess:backoffice');
    });

    /**
     * 회원 / 이메일 인증
     */
    Route::group(['prefix' => 'user/email-verification'], function () {
        // 이메일 인증링크 재발송 & 인증링크 클릭시 랜딩 페이지
        Route::post('', [UserController::class, 'resendVerificationEmail'])->middleware('auth:api');
        Route::get('/{verifyKey}/{id}', [UserController::class, 'verification'])->name('verification.verify');
    });

    /**
     * 회원 / 비밀번호 관련
     */
    Route::group(['prefix' => 'user/password'], function () {
        // 비밀번호 변경 링크 발송 & 유효성 체크 & 발송 후 변경
        Route::post('/reset-mail', [UserController::class, 'passwordResetSendLink']);
        Route::get('/reset-mail', [UserController::class, 'changePwdVerification']);
        Route::patch('/reset-mail', [UserController::class, 'passwordReset']);

        // 비밀번호 검증 & 비밀번호 변경
        Route::post('', [UserController::class, 'checkPassword'])->middleware('auth:api');
        Route::patch('', [UserController::class, 'modifyPassword'])->middleware('auth:api');
    });

    /**
     * 관리자 및 권한 관련
     */
    Route::group(['middleware' => 'chkAccess:backoffice'], function () {
        Route::resource('authority', AuthorityController::class);
        Route::resource('manager', ManagerController::class,[
            'only' => ['index', 'show', 'store', 'destroy']
        ]);
    });

    /**
     * 게시판 관련
     */
    Route::group(['prefix' => 'board', 'middleware' => 'chkAccess:backoffice'], function () {
        // 게시판 CRUD
        Route::post('', [BoardController::class, 'store']);
        Route::get('', [BoardController::class, 'index'])->withoutmiddleware('chkAccess:backoffice');
        Route::get('/{id}', [BoardController::class, 'show'])->withoutmiddleware('chkAccess:backoffice')->where(['id' => '[0-9]+']);
        Route::patch('/{id}', [BoardController::class, 'update']);
        Route::delete('/{id}', [BoardController::class, 'destroy']);

        // 게시판 옵션
        Route::get('/option', [OptionController::class, 'index']);

        // 게시글 CRUD
        Route::post('/{boardId}/post', [PostController::class, 'store']);
        Route::get('/{boardId}/post', [PostController::class, 'index'])->withoutmiddleware('chkAccess:backoffice');
        Route::get('/{boardId}/post/{id}', [PostController::class, 'show'])->withoutmiddleware('chkAccess:backoffice');
        Route::patch('/{boardId}/post/{id}', [PostController::class, 'update']);
        Route::delete('/{boardId}/post/{id}', [PostController::class, 'destroy']);

        // Unique API
        // 게시판의 게시글 수를 포함한 목록
        Route::get('/posts-count', [BoardController::class, 'getPostsCount']);

        // 게시판의 전시순서 변경
        Route::patch('/{id}/sort', [BoardController::class, 'updateBoardSort']);
    });

    // 게시판 글 목록 (Backoffice)
    Route::group(['prefix' => 'post', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::get('', [PostController::class, 'getList']);
    });

    /**
     * 게시판 덧글 관련
     */
    // 댓글 CRUD
    Route::group(['prefix' => 'board', 'middleware' => 'chkAccess:regular'], function () {
        Route::post('/{boardId}/post/{postId}/reply', [ReplyController::class, 'store']);
        Route::get('/{boardId}/post/{postId}/reply', [ReplyController::class, 'index'])->withoutmiddleware('chkAccess:regular');
        Route::patch('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'update']);
        Route::delete('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'destroy']);
    });

    /**
     * 1:1 문의
     */
    // 문의 CRUD
    Route::group(['prefix' => 'inquiry'], function () {
        Route::post('', [InquiryController::class, 'store'])->middleware('chkAccess:regular');
        Route::get('', [InquiryController::class, 'index'])->middleware('chkAccess:regular,backoffice');
        Route::get('{inquiryId}', [InquiryController::class, 'show'])->middleware('chkAccess:regular,backoffice');
        Route::patch('{inquiryId}', [InquiryController::class, 'update'])->middleware('chkAccess:regular');
        Route::delete('{inquiryId}', [InquiryController::class, 'destroy'])->middleware('chkAccess:regular');
    });

    // 답변 CRUD (Customized Router)
    Route::group(['prefix' => 'inquiry/{inquiryId}/answer', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::post('', [InquiryAnswerController::class, 'store']);
        Route::get('', [InquiryAnswerController::class, 'show']);
        Route::patch('', [InquiryAnswerController::class, 'update']);
        Route::delete('', [InquiryAnswerController::class, 'destroy']);
    });

    // 첨부파일
    Route::group([
        'prefix' => 'attach',
        'middleware' => 'chkAccess:associate,backoffice'
    ], function () {
        Route::post('', [AttachController::class, 'store']);            // 임시 파일 첨부
        Route::patch('/{id}', [AttachController::class, 'update']);     // 파일 이동
        Route::delete('/{id}', [AttachController::class, 'delete']);    // 파일 삭제
    });
});