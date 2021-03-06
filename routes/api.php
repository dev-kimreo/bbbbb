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
use App\Http\Controllers\Components\ComponentUsablePageController;
use App\Http\Controllers\Components\ComponentVersionController;
use App\Http\Controllers\EditablePages\EditablePageController;
use App\Http\Controllers\EditablePages\EditablePageLayoutController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExceptionController;
use App\Http\Controllers\Exhibitions\BannerController;
use App\Http\Controllers\Exhibitions\CategoryController as ExhibitionCategoryController;
use App\Http\Controllers\Exhibitions\PopupController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Inquiries\InquiryAnswerController;
use App\Http\Controllers\Inquiries\InquiryController;
use App\Http\Controllers\LinkedComponents\LinkedComponentController;
use App\Http\Controllers\LinkedComponents\LinkedComponentOptionController;
use App\Http\Controllers\LinkedComponents\ScriptRequestController;
use App\Http\Controllers\SolutionController;
use App\Http\Controllers\TermsOfUseController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Themes\ThemeBuildController;
use App\Http\Controllers\Themes\ThemeController;
use App\Http\Controllers\Themes\ThemeProductController;
use App\Http\Controllers\Themes\ThemeProductInformationController;
use App\Http\Controllers\TooltipController;
use App\Http\Controllers\Users\ManagerController;
use App\Http\Controllers\Users\UserAdvAgreeController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserSiteController;
use App\Http\Controllers\Users\UserSolutionController;
use App\Http\Controllers\UserThemes\UserEditablePageController;
use App\Http\Controllers\UserThemes\UserEditablePageLayoutController;
use App\Http\Controllers\UserThemes\UserThemeController;
use App\Http\Controllers\UserThemes\UserThemePurchaseHistoryController;
use App\Http\Controllers\UserThemes\UserThemeSaveHistoryController;
use App\Http\Controllers\Widgets\WidgetController;
use App\Http\Controllers\Widgets\WidgetUsageController;
use App\Http\Controllers\WordController;
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

Route::group(['prefix' => 'v1', 'middleware' => ['language', 'requestToSnake', 'responseToCamel']], function () {
    // TODO Front end ??? Referer ????????? ?????? ?????? controller ?????? ??? ??????
    Route::get('test', [TestController::class, 'test']);

    /**
     * ?????? ??????
     */
    Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
        // ?????? ?????? CRUD
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
            Route::patch('/{user_id}/grade-up', [UserController::class, 'gradeUp'])->where(['user_id' => '[0-9]+']);
        });

        // ?????? ?????? CRUD
        Route::post('/auth', [AccessTokenController::class, 'store'])
            ->withoutmiddleware('auth:api')
            ->middleware('chkAccess:guest');
        Route::get('/auth', [AccessTokenController::class, 'show']);
        Route::delete('/auth', [AccessTokenController::class, 'destroy']);

        // ?????? ?????? ????????? ??? ?????????
        Route::resource('/{user_id}/solution', UserSolutionController::class, [
            'only' => ['index', 'store', 'update', 'destroy']
        ])->middleware('chkAccess:owner,backoffice');

        Route::resource('/{user_id}/site', UserSiteController::class)
            ->middleware('chkAccess:owner,backoffice');

        // ??????????????? ???????????? ?????? CD (?????? ??? ??????)
        Route::patch('/{user_id}/adv-agree', [UserAdvAgreeController::class, 'update'])
            ->middleware('chkAccess:owner,backoffice');

        // ????????? Super Login
        Route::post('/{user_id}/auth', [UserController::class, 'personalClientLogin'])
            ->middleware('chkAccess:backoffice');
    });

    /**
     * ?????? / ????????? ??????
     */
    Route::group(['prefix' => 'user/email-verification'], function () {
        // ????????? ???????????? ????????? & ???????????? ????????? ?????? ?????????
        Route::post('', [UserController::class, 'resendVerificationEmail'])->middleware('auth:api');
        Route::get('/{verify_key}/{user_id}', [UserController::class, 'verification'])->name('verification.verify');
    });

    /**
     * ?????? / ???????????? ??????
     */
    Route::group(['prefix' => 'user/password'], function () {
        // ???????????? ?????? ?????? ?????? & ????????? ?????? & ?????? ??? ??????
        Route::post('/reset-mail', [UserController::class, 'passwordResetSendLink']);
        Route::get('/reset-mail', [UserController::class, 'changePwdVerification']);
        Route::patch('/reset-mail', [UserController::class, 'passwordReset']);

        // ???????????? ?????? & ???????????? ??????
        Route::post('', [UserController::class, 'checkPassword'])->middleware('auth:api');
        Route::patch('', [UserController::class, 'modifyPassword'])->middleware('auth:api');
    });

    /**
     * ????????? ??? ?????? ??????
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
     * ?????? CRUD
     */
    Route::group(['middleware' => 'chkAccess:backoffice'], function () {
        Route::resource('backoffice-menu', BackofficeMenuController::class);
    });

    /**
     * ?????? ?????? CRUD
     */
    Route::group(['middleware' => 'chkAccess:backoffice'], function () {
        Route::resource('backoffice-permission', BackofficePermissionController::class, [
            'only' => ['index', 'show', 'store', 'destroy']
        ]);
    });

    /**
     * ????????? ??????
     */
    Route::group(['prefix' => 'board', 'middleware' => 'chkAccess:backoffice'], function () {
        // ????????? CRUD
        Route::post('', [BoardController::class, 'store']);
        Route::get('', [BoardController::class, 'index'])->withoutmiddleware('chkAccess:backoffice');
        Route::get('/{id}', [BoardController::class, 'show'])->withoutmiddleware('chkAccess:backoffice')->where(
            ['id' => '[0-9]+']
        );
        Route::patch('/{id}', [BoardController::class, 'update'])->where(['id' => '[0-9]+']);
        Route::delete('/{id}', [BoardController::class, 'destroy']);

        // ????????? ??????
        Route::get('/option', [OptionController::class, 'index']);

        // ????????? CRUD
        Route::post('/{boardId}/post', [PostController::class, 'store']);
        Route::get('/{boardId}/post', [PostController::class, 'index'])->withoutmiddleware('chkAccess:backoffice');
        Route::get('/{boardId}/post/{id}', [PostController::class, 'show'])->withoutmiddleware('chkAccess:backoffice');
        Route::patch('/{boardId}/post/{id}', [PostController::class, 'update']);
        Route::delete('/{boardId}/post/{id}', [PostController::class, 'destroy']);

        // Unique API
        // ???????????? ????????? ?????? ????????? ??????
        Route::get('/posts-count', [BoardController::class, 'getPostsCount']);

        // ???????????? ???????????? ??????
        Route::patch('/sort', [BoardController::class, 'updateBoardSort']);
        // Route::patch('/{id}/sort', [BoardController::class, 'updateSelectBoardSort']);
    });

    // ????????? ??? ?????? (Backoffice)
    Route::group(['prefix' => 'post', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::get('', [PostController::class, 'getList']);
    });

    /**
     * ????????? ?????? ??????
     */
    // ?????? CRUD
    Route::group(['prefix' => 'board', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::post('/{boardId}/post/{postId}/reply', [ReplyController::class, 'store']);
        Route::get('/{boardId}/post/{postId}/reply', [ReplyController::class, 'index'])->withoutmiddleware(
            'chkAccess:backoffice'
        );
        Route::patch('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'update']);
        Route::delete('/{boardId}/post/{postId}/reply/{id}', [ReplyController::class, 'destroy']);
    });

    /**
     * 1:1 ??????
     */
    // ?????? CRUD
    Route::group(['prefix' => 'inquiry'], function () {
        Route::post('', [InquiryController::class, 'store'])->middleware('chkAccess:associate,regular');
        Route::get('', [InquiryController::class, 'index'])->middleware('chkAccess:associate,regular,backoffice');
        Route::get('{inquiryId}', [InquiryController::class, 'show'])->middleware(
            'chkAccess:associate,regular,backoffice'
        )->where(['inquiryId' => '[0-9]+']);
        Route::patch('{inquiryId}', [InquiryController::class, 'update'])->middleware('chkAccess:associate,regular');
        Route::delete('{inquiryId}', [InquiryController::class, 'destroy'])->middleware('chkAccess:associate,regular');

        // ????????? ??????
        Route::patch('{inquiryId}/assignee/{assignee_id}', [InquiryController::class, 'assignee'])->middleware(
            'chkAccess:backoffice'
        );
    });

    // ?????? CRUD (Customized Router)
    Route::group(['prefix' => 'inquiry/{inquiryId}/answer', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::post('', [InquiryAnswerController::class, 'store']);
        Route::get('', [InquiryAnswerController::class, 'show']);
        Route::patch('', [InquiryAnswerController::class, 'update']);
        Route::delete('', [InquiryAnswerController::class, 'destroy']);
    });

    // ????????????
    Route::group(['prefix' => 'attach', 'middleware' => 'chkAccess:associate,backoffice'], function () {
        Route::post('', [AttachController::class, 'store']);            // ?????? ?????? ??????
        Route::patch('/{id}', [AttachController::class, 'update']);     // ?????? ??????
        Route::delete('/{id}', [AttachController::class, 'delete']);    // ?????? ??????
    });

    // ???????????? ????????? ?????????
    Route::group(['prefix' => 'component-upload-image', 'middleware' => 'chkAccess:associate,backoffice'], function () {
        Route::get('', [ComponentUploadImageController::class, 'index']);
        Route::get('/{id}', [ComponentUploadImageController::class, 'show'])->where(['id' => '^[0-9]+$']);
        Route::post('', [ComponentUploadImageController::class, 'store']);
        Route::delete('/{id}', [ComponentUploadImageController::class, 'destroy']);
        Route::get('/usage', [ComponentUploadImageController::class, 'usage']);
    });

    // ???????????? & ???????????? ?????? ??????
    Route::group(['prefix' => 'terms-of-use', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::post('', [TermsOfUseController::class, 'store']);
        Route::get('', [TermsOfUseController::class, 'index'])
            ->withoutmiddleware('chkAccess:backoffice');
        Route::get('/{terms_of_use_id}', [TermsOfUseController::class, 'show'])
            ->where(['terms_of_use_id' => '[0-9]+'])
            ->withoutmiddleware('chkAccess:backoffice');
        Route::patch('/{terms_of_use_id}', [TermsOfUseController::class, 'update']);
        Route::delete('/{terms_of_use_id}', [TermsOfUseController::class, 'destroy']);

        Route::get('/current', [TermsOfUseController::class, 'getCurrent'])
            ->withoutmiddleware('chkAccess:backoffice');
        Route::get('/service', [TermsOfUseController::class, 'getServiceList'])
            ->withoutmiddleware('chkAccess:backoffice');
        Route::get('/type', [TermsOfUseController::class, 'getTypeList'])
            ->withoutmiddleware('chkAccess:backoffice');
    });

    /**
     * ??????
     */
    Route::group(['prefix' => 'tooltip'], function () {
        Route::post('', [TooltipController::class, 'store'])->middleware('chkAccess:backoffice');
        Route::get('', [TooltipController::class, 'index']);
        Route::get('/{tooltip_id}', [TooltipController::class, 'show']);
        Route::patch('/{tooltip_id}', [TooltipController::class, 'update'])->middleware('chkAccess:backoffice');
        Route::delete('/{tooltip_id}', [TooltipController::class, 'destroy'])->middleware('chkAccess:backoffice');
    });

    /**
     * ????????????
     */
    Route::group(['prefix' => 'exhibition'], function () {
        // ???????????? ????????????
        Route::resource('/category', ExhibitionCategoryController::class)
            ->middleware('chkAccess:backoffice');

        // ????????????
        Route::resource('/popup', PopupController::class, [
            'only' => ['store', 'update', 'destroy', 'show']
        ])->middleware('chkAccess:backoffice');
        Route::resource('/popup', PopupController::class, [
            'only' => ['index']
        ]);

        // ????????????
        Route::resource('/banner', BannerController::class, [
            'only' => ['store', 'update', 'destroy', 'show']
        ])->middleware('chkAccess:backoffice');
        Route::resource('/banner', BannerController::class, [
            'only' => ['index']
        ]);
    });

    /**
     * ??????????????? ??????
     */
    // ????????? ?????????
    Route::group(['prefix' => 'email-template', 'middleware' => 'chkAccess:backoffice'], function () {
        Route::get('', [EmailTemplateController::class, 'index']);
        Route::get('/{email_template_id}', [EmailTemplateController::class, 'show']);
        Route::post('', [EmailTemplateController::class, 'store']);
        Route::patch('/{email_template_id}', [EmailTemplateController::class, 'update']);
        Route::delete('/{email_template_id}', [EmailTemplateController::class, 'destroy']);
    });

    // ????????????
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

    // ?????????
    Route::resource('/solution', SolutionController::class, [
        'only' => ['store', 'update', 'destroy']
    ])->middleware('chkAccess:backoffice');
    Route::resource('/solution', SolutionController::class, [
        'only' => ['show', 'index']
    ]);

    // ?????? ??????
    Route::group(['prefix' => 'theme-product', 'middleware' => ['auth:api', 'chkAccess:partner']], function () {
        Route::get('', [ThemeProductController::class, 'index']);
        Route::get('/{theme_product_id}', [ThemeProductController::class, 'show']);
        Route::post('', [ThemeProductController::class, 'store']);
        Route::patch('/{theme_product_id}', [ThemeProductController::class, 'update']);
        Route::delete('/{theme_product_id}', [ThemeProductController::class, 'destroy']);

        // ????????????
        Route::group(['prefix' => '{theme_product_id}/information'], function () {
            Route::get('', [ThemeProductInformationController::class, 'index']);
            Route::get('/{information_id}', [ThemeProductInformationController::class, 'show']);
            Route::post('', [ThemeProductInformationController::class, 'store']);
            Route::patch('/{information_id}', [ThemeProductInformationController::class, 'update']);
            Route::delete('/{information_id}', [ThemeProductInformationController::class, 'destroy']);
        });


        // ??????
        Route::get('/{theme_product_id}/theme', [ThemeController::class, 'index']);
        Route::get('/{theme_product_id}/theme/{theme_id}', [ThemeController::class, 'show']);
        Route::post('/{theme_product_id}/theme', [ThemeController::class, 'store']);
        Route::patch('/{theme_product_id}/theme/{theme_id}', [ThemeController::class, 'update']);
        Route::delete('/{theme_product_id}/theme/{theme_id}', [ThemeController::class, 'destroy']);

        // ????????? ?????? (Non-CRUD)
        Route::post('/{theme_product_id}/relational-theme', [ThemeController::class, 'relationalStore']);
    });

    // ??????
    Route::group(['prefix' => 'theme', 'middleware' => ['auth:api', 'chkAccess:partner']], function () {
        Route::get('', [ThemeController::class, 'index']);
        Route::get('/{theme_id}', [ThemeController::class, 'show']);
        Route::patch('/{theme_id}', [ThemeController::class, 'update']);
        Route::delete('/{theme_id}', [ThemeController::class, 'destroy']);

        // ????????? ?????? ????????? ??????
        Route::get('/{theme_id}/editable-page', [EditablePageController::class, 'index']);
        Route::get('/{theme_id}/editable-page/{editable_page_id}', [EditablePageController::class, 'show']);
        Route::post('/{theme_id}/editable-page', [EditablePageController::class, 'store']);
        Route::patch('/{theme_id}/editable-page/{editable_page_id}', [EditablePageController::class, 'update']);
        Route::delete('/{theme_id}/editable-page/{editable_page_id}', [EditablePageController::class, 'destroy']);

        // ????????? ?????? ????????? ???????????? ??????
        Route::get('/{theme_id}/editable-page/{editable_page_id}/layout', [EditablePageLayoutController::class, 'index']
        );
        Route::get(
            '/{theme_id}/editable-page/{editable_page_id}/layout/{layout_id}',
            [EditablePageLayoutController::class, 'show']
        );
        Route::post(
            '/{theme_id}/editable-page/{editable_page_id}/layout',
            [EditablePageLayoutController::class, 'store']
        );
        Route::patch(
            '/{theme_id}/editable-page/{editable_page_id}/layout/{layout_id}',
            [EditablePageLayoutController::class, 'update']
        );
        Route::delete(
            '/{theme_id}/editable-page/{editable_page_id}/layout/{layout_id}',
            [EditablePageLayoutController::class, 'destroy']
        );

        // ?????? ????????????
        Route::get(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component',
            [LinkedComponentController::class, 'index']
        );
        Route::get(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}',
            [LinkedComponentController::class, 'show']
        );
        Route::post(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component',
            [LinkedComponentController::class, 'store']
        );
        Route::patch(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}',
            [LinkedComponentController::class, 'update']
        );
        Route::delete(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}',
            [LinkedComponentController::class, 'destroy']
        );
        Route::post(
            '/{theme_id}/editable-page/{editable_page_id}/relational-linked-component',
            [LinkedComponentController::class, 'relationalLinkedComponent']
        );

        // ?????? ???????????? ??????
        Route::get(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option',
            [LinkedComponentOptionController::class, 'index']
        );
        Route::get(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{linked_component_option_id}',
            [LinkedComponentOptionController::class, 'show']
        );
        Route::post(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option',
            [LinkedComponentOptionController::class, 'store']
        );
        Route::patch(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{linked_component_option_id}',
            [LinkedComponentOptionController::class, 'update']
        );
        Route::delete(
            '/{theme_id}/editable-page/{editable_page_id}/linked-component/{linked_component_id}/option/{linked_component_option_id}',
            [LinkedComponentOptionController::class, 'destroy']
        );

        // ??????
        Route::get('/{theme_id}/build', [ThemeBuildController::class, 'build']);
        Route::post('/{theme_id}/export', [ThemeBuildController::class, 'export']);
    });

    /**
     * ?????? ???????????? (Shortcut)
     */
    Route::group(
        ['prefix' => 'linked-component', 'middleware' => ['auth:api', 'chkAccess:partner']],
        function () {
            Route::get('/{linked_component_id}', [LinkedComponentController::class, 'showDirectly']);
        }
    );

    /**
     * ????????????
     */
    Route::group(['prefix' => 'component', 'middleware' => ['auth:api', 'chkAccess:partner']], function () {
        Route::get('', [ComponentController::class, 'index']);
        Route::get('/{component_id}', [ComponentController::class, 'show']);
        Route::post('', [ComponentController::class, 'store']);
        Route::patch('/{component_id}', [ComponentController::class, 'update']);
        Route::delete('/{component_id}', [ComponentController::class, 'destroy']);

        // ???????????? ??????
        Route::get('/{component_id}/version', [ComponentVersionController::class, 'index']);
        Route::get('/{component_id}/version/{version_id}', [ComponentVersionController::class, 'show']);
        Route::post('/{component_id}/version', [ComponentVersionController::class, 'store']);
        Route::patch('/{component_id}/version/{version_id}', [ComponentVersionController::class, 'update']);
        Route::delete('/{component_id}/version/{version_id}', [ComponentVersionController::class, 'destroy']);

        Route::patch('/{component_id}/activate-version/{version_id}', [ComponentVersionController::class, 'activate']);

        // ???????????? ??????
        Route::get('/{component_id}/version/{version_id}/option', [ComponentOptionController::class, 'index']);
        Route::get('/{component_id}/version/{version_id}/option/{option_id}', [ComponentOptionController::class, 'show']
        );
        Route::post('/{component_id}/version/{version_id}/option', [ComponentOptionController::class, 'store']);
        Route::patch(
            '/{component_id}/version/{version_id}/option/{option_id}',
            [ComponentOptionController::class, 'update']
        );
        Route::delete(
            '/{component_id}/version/{version_id}/option/{option_id}',
            [ComponentOptionController::class, 'destroy']
        );

        Route::post(
            '/{component_id}/version/{version_id}/relational-option',
            [ComponentOptionController::class, 'relationalStore']
        );
    });

    // ???????????? ?????? ?????????
    Route::group(['prefix' => 'component-usable-page', 'middleware' => ['auth:api', 'chkAccess:partner']], function () {
        Route::get('', [ComponentUsablePageController::class, 'index']);
        Route::get('/{component_usable_page_id}', [ComponentUsablePageController::class, 'show']);
        Route::post('', [ComponentUsablePageController::class, 'store']);
        Route::delete('/{component_usable_page_id}', [ComponentUsablePageController::class, 'destroy']);
    });

    // ???????????? ?????? ??????
    Route::group(['prefix' => 'component-option', 'middleware' => ['auth:api', 'chkAccess:partner']], function () {
        Route::get('', [ComponentOptionController::class, 'index']);
        Route::get('/{option_id}', [ComponentOptionController::class, 'show']);
    });

    // ???????????? ?????? ??????
    Route::group(['prefix' => 'component-type', 'middleware' => ['auth:api', 'chkAccess:partner']], function () {
        Route::get('', [ComponentTypeController::class, 'index']);
        Route::get('/{type_id}', [ComponentTypeController::class, 'show']);
        Route::post('', [ComponentTypeController::class, 'store']);
        Route::patch('/{type_id}', [ComponentTypeController::class, 'update']);
        Route::delete('/{type_id}', [ComponentTypeController::class, 'destroy']);

        // ???????????? ?????? ?????? ??????
        Route::get('/{type_id}/property', [ComponentTypePropertyController::class, 'index']);
        Route::get('/{type_id}/property/{property_id}', [ComponentTypePropertyController::class, 'show']);
        Route::post('/{type_id}/property', [ComponentTypePropertyController::class, 'store']);
        Route::patch('/{type_id}/property/{property_id}', [ComponentTypePropertyController::class, 'update']);
        Route::delete('/{type_id}/property/{property_id}', [ComponentTypePropertyController::class, 'destroy']);
    });


    /**
     * ???????????? Script Request API
     */
    Route::get('/component/script/{hash}.js', [ScriptRequestController::class, 'show'])
        ->withoutMiddleware([ConvertResponseToCamelCase::class]);


    /**
     * ?????? ??????
     */
    Route::group(['prefix' => 'user-theme', 'middleware' => ['auth:api', 'chkAccess:associate,regular']], function () {
        Route::get('', [UserThemeController::class, 'index']);
        Route::get('/{user_theme_id}', [UserThemeController::class, 'show']);
        Route::post('', [UserThemeController::class, 'store']);
        Route::patch('/{user_theme_id}', [UserThemeController::class, 'update']);
        Route::delete('/{user_theme_id}', [UserThemeController::class, 'destroy']);

        /**
         * ?????? ????????? ?????? ????????? ??????
         */
        Route::get('/{user_theme_id}/editable-page', [UserEditablePageController::class, 'index']);
        Route::get('/{user_theme_id}/editable-page/{editable_page_id}', [UserEditablePageController::class, 'show']);
        Route::post('/{user_theme_id}/editable-page', [UserEditablePageController::class, 'store']);
        Route::patch('/{user_theme_id}/editable-page/{editable_page_id}', [UserEditablePageController::class, 'update']
        );
        Route::delete(
            '/{user_theme_id}/editable-page/{editable_page_id}',
            [UserEditablePageController::class, 'destroy']
        );

        /**
         * ?????? ????????? ?????? ????????? ????????????
         */
        Route::get('/{user_theme_id}/editable-page/{editable_page_id}/layout', [UserEditablePageLayoutController::class, 'index']);
        Route::get('/{user_theme_id}/editable-page/{editable_page_id}/layout/{layout_id}', [UserEditablePageLayoutController::class, 'show']);
        Route::post('/{user_theme_id}/editable-page/{editable_page_id}/layout', [UserEditablePageLayoutController::class, 'store']);
        Route::patch('/{user_theme_id}/editable-page/{editable_page_id}/layout/{layout_id}', [UserEditablePageLayoutController::class, 'update']);
        Route::delete('/{user_theme_id}/editable-page/{editable_page_id}/layout/{layout_id}', [UserEditablePageLayoutController::class, 'destroy']);
    });

    /**
     * ?????? ????????????
     */
    Route::group(
        [
            'prefix' => 'user-theme/{user_theme_id}/save-history',
            'middleware' => ['auth:api', 'chkAccess:associate,regular,backoffice']
        ],
        function () {
            Route::get('', [UserThemeSaveHistoryController::class, 'index']);
            Route::get('/{history_id}', [UserThemeSaveHistoryController::class, 'show']);
            Route::post('', [UserThemeSaveHistoryController::class, 'store']);
            Route::delete('/{history_id}', [UserThemeSaveHistoryController::class, 'destroy']);
        }
    );

    /**
     * ?????? ?????? ????????????
     */
    Route::group(
        ['prefix' => 'user-theme-purchase-history', 'middleware' => ['auth:api', 'chkAccess:associate,regular']],
        function () {
            Route::get('', [UserThemePurchaseHistoryController::class, 'index']);
            Route::get('/{user_theme_purchase_history_id}', [UserThemePurchaseHistoryController::class, 'show']);
            Route::post('', [UserThemePurchaseHistoryController::class, 'store']);
            Route::delete('/{user_theme_purchase_history_id}', [UserThemePurchaseHistoryController::class, 'destroy']);
        }
    );


    /**
     * Exception
     */
    Route::group(['prefix' => 'exception', 'middleware' => ['auth:api', 'chkAccess:backoffice']], function () {
        Route::get('', [ExceptionController::class, 'index']);
        Route::get('/{exception_id}', [ExceptionController::class, 'show']);
//        Route::post('', [ExceptionController::class, 'store']);
        Route::patch('/{exception_id}', [ExceptionController::class, 'update']);
        Route::delete('/{exception_id}', [ExceptionController::class, 'destroy']);
    });
    Route::post('/relation-exception', [ExceptionController::class, 'relationStore']);
    Route::get('/exception-to-json', [ExceptionController::class, 'responseInJsonFormat']);
    /**
     * End
     */

    /**
     * Word
     */
    Route::group(['prefix' => 'word', 'middleware' => ['auth:api', 'chkAccess:backoffice']], function () {
        Route::get('', [WordController::class, 'index']);
        Route::get('/{word_id}', [WordController::class, 'show']);
//        Route::post('', [WordController::class, 'store']);
        Route::patch('/{word_id}', [WordController::class, 'update']);
        Route::delete('/{word_id}', [WordController::class, 'destroy']);
    });
    Route::post('/relation-word', [WordController::class, 'relationStore']);
    Route::get('/word-to-json', [WordController::class, 'responseInJsonFormat']);


    /**
     * Export
     */
    Route::group(['prefix' => 'export', 'middleware' => ['auth:api', 'chkAccess:backoffice']], function () {
        Route::get('/excel', [ExportController::class, 'excel']);
    });

    /**
     * ??????
     */
    Route::group(['prefix' => 'statistics', 'middleware' => ['auth:api', 'chkAccess:backoffice']], function () {
        // User
        Route::get('user/count-per-grade', [UserController::class, 'getStatUserByGrade']);
        Route::get('user/login-log/count-per-grade', [UserController::class, 'getCountLoginLogPerGrade']);

        // Inquiry
        Route::get('inquiry/count-per-status', [InquiryController::class, 'getCountPerStatus']);
    });
});
