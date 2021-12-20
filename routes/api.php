<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\Attach\AttachController;
use App\Http\Controllers\Attach\ComponentUploadImageController;
use App\Http\Controllers\AuthorityController;
use App\Http\Controllers\BackofficeMenuController;
use App\Http\Controllers\BackofficePermissionController;
use App\Http\Controllers\Boards\BoardController;
use App\Http\Controllers\Boards\OptionController;
use App\Http\Controllers\Boards\PostController;
use App\Http\Controllers\Boards\ReplyController;
use App\Http\Controllers\Components\ComponentController;
use App\Http\Controllers\Components\ComponentOptionController;
use App\Http\Controllers\Components\ComponentTypeController;
use App\Http\Controllers\Components\ComponentTypePropertyController;
use App\Http\Controllers\Components\ComponentVersionController;
use App\Http\Controllers\EditablePages\EditablePageController;
use App\Http\Controllers\EditablePages\EditablePageLayoutController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\Exhibitions\BannerController;
use App\Http\Controllers\Exhibitions\CategoryController as ExhibitionCategoryController;
use App\Http\Controllers\Exhibitions\PopupController;
use App\Http\Controllers\Inquiries\InquiryAnswerController;
use App\Http\Controllers\Inquiries\InquiryController;
use App\Http\Controllers\LinkedComponents\LinkedComponentController;
use App\Http\Controllers\LinkedComponents\LinkedComponentOptionController;
use App\Http\Controllers\LinkedComponents\ScriptRequestController;
use App\Http\Controllers\SolutionController;
use App\Http\Controllers\TermsOfUseController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Themes\ThemeController;
use App\Http\Controllers\Themes\ThemeProductController;
use App\Http\Controllers\TooltipController;
use App\Http\Controllers\Users\ManagerController;
use App\Http\Controllers\Users\UserAdvAgreeController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserSiteController;
use App\Http\Controllers\Widgets\WidgetController;
use App\Http\Controllers\Widgets\WidgetUsageController;
use App\Http\Middleware\ConvertResponseToCamelCase;
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
    // TODO Front end 의 Referer 체크를 위한 임시 controller 체크 후 삭제
    Route::get('test', [TestController::class, 'test']);

    /**
     * 회원 관련
     */
    Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
        // 회원 관련 CRUD
        Route::post('', [UserController::class, 'store'])
            ->withoutmiddleware('auth:api')
            ->middleware('chkAccess:guest');
        Route::get('', [UserController::class, 'index'])
            ->middleware('chkAccess:backoffice');
        Route::group(['middleware' => 'chkAccess:owner,backoffice'], function () {
            Route::get('/{user_id}', [UserController::class, 'show'])->where(['user_id' => '[0-9]+']);
            Route::patch('/{user_id}', [UserController::class, 'update'])->where(['user_id' => '[0-9]+']);
            Route::delete('/{user_id}', [UserController::class, 'destroy'])->where(['user_id' => '[0-9]+']);
            Route::get('/{user_id}/login-log', [UserController::class, 'getLoginLog'])->where(['user_id' => '[0-9]+']);
            Route::get('/{user_id}/action-log', [UserController::class, 'getActionLog'])->where(['user_id' => '[0-9]+']);
        });

        // 회원 세션 CRUD
        Route::post('/auth', [AccessTokenController::class, 'store'])
            ->withoutmiddleware('auth:api')
            ->middleware('chkAccess:guest');
        Route::get('/auth', [AccessTokenController::class, 'show']);
        Route::delete('/auth', [AccessTokenController::class, 'destroy']);

        // 회원 연동 솔루션 CD (추가 및 삭제)
        Route::resource('/{user_id}/site', UserSiteController::class, [
            'only' => ['store', 'update', 'destroy']
        ])->middleware('chkAccess:owner,backoffice');

        // 광고성정보 수신동의 여부 CD (동의 및 거부)
        Route::patch('/{user_id}/adv-agree', [UserAdvAgreeController::class, 'update'])
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
        Route::get('/{verifyKey}/{user_id}', [UserController::class, 'verification'])->name('verification.verify');
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
        Route::group(['prefix' => 'authority'], function () {
            Route::post('', [AuthorityController::class, 'store']);
            Route::get('', [AuthorityController::class, 'index']);
            Route::get('/{id}', [AuthorityController::class, 'show'])->where(['id' => '[0-9]+']);
            Route::patch('/{id}', [AuthorityController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::delete('/{id}', [AuthorityController::class, 'destroy']);

            Route::get('/{id}/menu-permission', [AuthorityController::class, 'getMenuListWithPermission']);
        });


        Route::resource('manager', ManagerController::class);
    });

    /**
     * 메뉴 CRUD
     */
    Route::group(['middleware' => 'chkAccess:backoffice'], function () {
        Route::resource('backoffice-menu', BackofficeMenuController::class);
    });

    /**
     * 메뉴 권한 CRUD
     */
    Route::group(['middleware' => 'chkAccess:backoffice'], function () {
        Route::resource('backoffice-permission', BackofficePermissionController::class, [
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
    Route::group(['prefix' => 'board', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::post('/{boardId}/post/{postId}/reply', [ReplyController::class, 'store']);
        Route::get('/{boardId}/post/{postId}/reply', [ReplyController::class, 'index'])->withoutmiddleware('chkAccess:backoffice');
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
        Route::get('{inquiryId}', [InquiryController::class, 'show'])->middleware('chkAccess:regular,backoffice')->where(['inquiryId' => '[0-9]+']);
        Route::patch('{inquiryId}', [InquiryController::class, 'update'])->middleware('chkAccess:regular');
        Route::delete('{inquiryId}', [InquiryController::class, 'destroy'])->middleware('chkAccess:regular');

        // 담당자 지정
        Route::patch('{inquiryId}/assignee/{assignee_id}', [InquiryController::class, 'assignee'])->middleware('chkAccess:backoffice');
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

    // 컴포넌트 업로드 이미지
    Route::group(
        [
            'prefix' => 'component-upload-image',
            'middleware' => 'chkAccess:associate,backoffice'
        ],
        function () {
            Route::get('', [ComponentUploadImageController::class, 'index']);
            Route::get('/{id}', [ComponentUploadImageController::class, 'show']);
            Route::post('', [ComponentUploadImageController::class, 'store']);
            Route::delete('/{id}', [ComponentUploadImageController::class, 'destroy']);
        }
    );

    // 이용약관 & 개인정보 처리 방침
    Route::group([
        'prefix' => 'terms-of-use',
        'middleware' => 'chkAccess:backoffice'
    ], function () {
        Route::post('', [TermsOfUseController::class, 'store']);
        Route::get('', [TermsOfUseController::class, 'index']);
        Route::get('/{terms_of_use_id}', [TermsOfUseController::class, 'show'])->where(['terms_of_use_id' => '[0-9]+'])->withoutmiddleware('chkAccess:backoffice');
        Route::patch('/{terms_of_use_id}', [TermsOfUseController::class, 'update']);
        Route::delete('/{terms_of_use_id}', [TermsOfUseController::class, 'destroy']);

        Route::get('/service', [TermsOfUseController::class, 'getServiceList'])->withoutmiddleware('chkAccess:backoffice');
        Route::get('/type', [TermsOfUseController::class, 'getTypeList'])->withoutmiddleware('chkAccess:backoffice');
    });

    /**
     * 툴팁
     */
    Route::group(['prefix' => 'tooltip'], function () {
        Route::post('', [TooltipController::class, 'store'])->middleware('chkAccess:backoffice');
        Route::get('', [TooltipController::class, 'index']);
        Route::get('/{tooltip_id}', [TooltipController::class, 'show']);
        Route::patch('/{tooltip_id}', [TooltipController::class, 'update'])->middleware('chkAccess:backoffice');
        Route::delete('/{tooltip_id}', [TooltipController::class, 'destroy'])->middleware('chkAccess:backoffice');
    });

    /**
     * 전시관리
     */
    Route::group(['prefix' => 'exhibition'], function () {
        // 전시관리 카테고리
        Route::resource('/category', ExhibitionCategoryController::class)
            ->middleware('chkAccess:backoffice');

        // 팝업관리
        Route::resource('/popup', PopupController::class, [
            'only' => ['store', 'update', 'destroy', 'show']
        ])->middleware('chkAccess:backoffice');
        Route::resource('/popup', PopupController::class, [
            'only' => ['index']
        ]);

        // 배너관리
        Route::resource('/banner', BannerController::class, [
            'only' => ['store', 'update', 'destroy', 'show']
        ])->middleware('chkAccess:backoffice');
        Route::resource('/banner', BannerController::class, [
            'only' => ['index']
        ]);
    });

    /**
     * 라이브러리 관리
     */
    // 이메일 템플릿
    Route::group([
        'prefix' => 'email-template',
        'middleware' => 'chkAccess:backoffice'
    ], function () {
        Route::get('', [EmailTemplateController::class, 'index']);
        Route::get('/{email_template_id}', [EmailTemplateController::class, 'show']);
        Route::post('', [EmailTemplateController::class, 'store']);
        Route::patch('/{email_template_id}', [EmailTemplateController::class, 'update']);
        Route::delete('/{email_template_id}', [EmailTemplateController::class, 'destroy']);
    });

    // 위젯관리
    Route::resource('/widget/usage', WidgetUsageController::class, [
        'only' => ['index', 'store', 'destroy', 'show']
    ])->middleware('chkAccess:associate,backoffice');
    Route::patch('/widget/usage/{id}/sort', [WidgetUsageController::class, 'sort'])
        ->middleware('chkAccess:associate,backoffice');
    Route::resource('/widget', WidgetController::class, [
        'only' => ['store', 'update', 'destroy']
    ])->middleware('chkAccess:backoffice');
    Route::resource('/widget', WidgetController::class, [
        'only' => ['index', 'show']
    ]);


    /**
     * Core Entity
     */

    // 솔루션
    Route::resource('/solution', SolutionController::class, [
        'only' => ['store', 'update', 'destroy']
    ])->middleware('chkAccess:backoffice');
    Route::resource('/solution', SolutionController::class, [
        'only' => ['show', 'index']
    ]);

    // 테마 상품
    Route::group([
        'prefix' => 'theme-product',
        'middleware' => ['auth:api', 'chkAccess:partner']
    ], function () {
        Route::get('', [ThemeProductController::class, 'index']);
        Route::get('/{theme_product_id}', [ThemeProductController::class, 'show']);
        Route::post('', [ThemeProductController::class, 'store']);
        Route::patch('/{theme_product_id}', [ThemeProductController::class, 'update']);
        Route::delete('/{theme_product_id}', [ThemeProductController::class, 'destroy']);

        // 테마
        Route::get('/{theme_product_id}/theme', [ThemeController::class, 'index']);
        Route::get('/{theme_product_id}/theme/{theme_id}', [ThemeController::class, 'show']);
        Route::post('/{theme_product_id}/theme', [ThemeController::class, 'store']);
        Route::patch('/{theme_product_id}/theme/{theme_id}', [ThemeController::class, 'update']);
        Route::delete('/{theme_product_id}/theme/{theme_id}', [ThemeController::class, 'destroy']);

        // 관계형 테마 (Non-CRUD)
        Route::post('/{theme_product_id}/relational-theme', [ThemeController::class, 'relationalStore']);
    });

    // 테마
    Route::group([
        'prefix' => 'theme',
        'middleware' => ['auth:api', 'chkAccess:partner']
    ], function () {
        Route::get('', [ThemeController::class, 'index']);
        Route::get('/{theme_id}', [ThemeController::class, 'show']);
        Route::patch('/{theme_id}', [ThemeController::class, 'update']);
        Route::delete('/{theme_id}', [ThemeController::class, 'destroy']);

        // 에디터 지원 페이지 목록
        Route::get('/{theme_id}/editable-page', [EditablePageController::class, 'index']);
        Route::get('/{theme_id}/editable-page/{editable_page_id}', [EditablePageController::class, 'show']);
        Route::post('/{theme_id}/editable-page', [EditablePageController::class, 'store']);
        Route::patch('/{theme_id}/editable-page/{editable_page_id}', [EditablePageController::class, 'update']);
        Route::delete('/{theme_id}/editable-page/{editable_page_id}', [EditablePageController::class, 'destroy']);

        // 에디터 지원 페이지 레이아웃 목록
        Route::get('/{theme_id}/editable-page/{editable_page_id}/layout', [EditablePageLayoutController::class, 'index']);
        Route::get('/{theme_id}/editable-page/{editable_page_id}/layout/{layout_id}', [EditablePageLayoutController::class, 'show']);
        Route::post('/{theme_id}/editable-page/{editable_page_id}/layout', [EditablePageLayoutController::class, 'store']);
        Route::patch('/{theme_id}/editable-page/{editable_page_id}/layout/{layout_id}', [EditablePageLayoutController::class, 'update']);
        Route::delete('/{theme_id}/editable-page/{editable_page_id}/layout/{layout_id}', [EditablePageLayoutController::class, 'destroy']);

        // 연동 컴포넌트
        Route::get('/{theme_id}/editable-page/{editable_page_id}/linked-component', [LinkedComponentController::class, 'index']);
        Route::get('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}', [LinkedComponentController::class, 'show']);
        Route::post('/{theme_id}/editable-page/{editable_page_id}/linked-component', [LinkedComponentController::class, 'store']);
        Route::patch('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}', [LinkedComponentController::class, 'update']);
        Route::delete('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}', [LinkedComponentController::class, 'destroy']);
        Route::post('/{theme_id}/editable-page/{editable_page_id}/relational-linked-component', [LinkedComponentController::class, 'relationalLinkedComponent']);

        // 연동 컴포넌트 옵션
        Route::get('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option', [LinkedComponentOptionController::class, 'index']);
        Route::get('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{linked_component_option_id}', [LinkedComponentOptionController::class, 'show']);
        Route::post('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option', [LinkedComponentOptionController::class, 'store']);
        Route::patch('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{linked_component_option_id}', [LinkedComponentOptionController::class, 'update']);
        Route::delete('/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{linked_component_option_id}', [LinkedComponentOptionController::class, 'destroy']);
    });

    /**
     * 컴포넌트
     */
    Route::group([
        'prefix' => 'component',
        'middleware' => ['auth:api', 'chkAccess:partner']
    ], function () {
        Route::get('', [ComponentController::class, 'index']);
        Route::get('/{component_id}', [ComponentController::class, 'show']);
        Route::post('', [ComponentController::class, 'store']);
        Route::patch('/{component_id}', [ComponentController::class, 'update']);
        Route::delete('/{component_id}', [ComponentController::class, 'destroy']);

        // 컴포넌트 버전
        Route::get('/{component_id}/version', [ComponentVersionController::class, 'index']);
        Route::get('/{component_id}/version/{version_id}', [ComponentVersionController::class, 'show']);
        Route::post('/{component_id}/version', [ComponentVersionController::class, 'store']);
        Route::patch('/{component_id}/version/{version_id}', [ComponentVersionController::class, 'update']);
        Route::delete('/{component_id}/version/{version_id}', [ComponentVersionController::class, 'destroy']);

        Route::patch('/{component_id}/activate-version/{version_id}', [ComponentVersionController::class, 'activate']);

        // 컴포넌트 옵션
        Route::get('/{component_id}/version/{version_id}/option', [ComponentOptionController::class, 'index']);
        Route::get('/{component_id}/version/{version_id}/option/{option_id}', [ComponentOptionController::class, 'show']);
        Route::post('/{component_id}/version/{version_id}/option', [ComponentOptionController::class, 'store']);
        Route::patch('/{component_id}/version/{version_id}/option/{option_id}', [ComponentOptionController::class, 'update']);
        Route::delete('/{component_id}/version/{version_id}/option/{option_id}', [ComponentOptionController::class, 'destroy']);

        Route::post('/{component_id}/version/{version_id}/relational-option', [ComponentOptionController::class, 'relationalStore']);

    });

    // 컴포넌트 옵션 유형
    Route::group([
        'prefix' => 'component-type',
        'middleware' => ['auth:api', 'chkAccess:partner']
    ], function () {
        Route::get('', [ComponentTypeController::class, 'index']);
        Route::get('/{type_id}', [ComponentTypeController::class, 'show']);
        Route::post('', [ComponentTypeController::class, 'store']);
        Route::patch('/{type_id}', [ComponentTypeController::class, 'update']);
        Route::delete('/{type_id}', [ComponentTypeController::class, 'destroy']);

        // 컴포넌트 옵션 유형 속성
        Route::get('/{type_id}/property', [ComponentTypePropertyController::class, 'index']);
        Route::get('/{type_id}/property/{property_id}', [ComponentTypePropertyController::class, 'show']);
        Route::post('/{type_id}/property', [ComponentTypePropertyController::class, 'store']);
        Route::patch('/{type_id}/property/{property_id}', [ComponentTypePropertyController::class, 'update']);
    });


    /**
     * 컴포넌트 Script Request API
     */
    Route::get('/component/script/{hash}.js', [ScriptRequestController::class, 'show'])
        ->withoutMiddleware([ConvertResponseToCamelCase::class]);

    /**
     * 통계
     */
    Route::group([
        'prefix' => 'statistics',
        'middleware' => ['auth:api', 'chkAccess:backoffice']
    ], function () {
        // User
        Route::get('user/count-per-grade', [UserController::class, 'getStatUserByGrade']);
        Route::get('user/login-log/count-per-grade', [UserController::class, 'getCountLoginLogPerGrade']);

        // Inquiry
        Route::get('inquiry/count-per-status', [InquiryController::class, 'getCountPerStatus']);
    });
});
