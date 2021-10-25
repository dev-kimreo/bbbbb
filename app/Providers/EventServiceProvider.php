<?php

namespace App\Providers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Events\Member\Login;
use App\Events\Member\Logout;
use App\Listeners\RemainActionLog;
use App\Models\Boards\Board;
use App\Models\Exhibitions\Banner;
use App\Models\Exhibitions\BannerDeviceContent;
use App\Models\Exhibitions\Exhibition;
use App\Models\Exhibitions\ExhibitionCategory;
use App\Models\Exhibitions\ExhibitionTargetUser;
use App\Models\Exhibitions\Popup;
use App\Models\Exhibitions\PopupDeviceContent;
use App\Models\Inquiries\InquiryAnswer;
use App\Models\EmailTemplate;
use App\Models\Boards\Post;
use App\Models\TermsOfUse;
use App\Models\Tooltip;
use App\Models\Users\User;
use App\Models\Users\UserAdvAgree;
use App\Models\Users\UserSite;
use App\Observers\BoardObserver;
use App\Observers\Exhibitions\BannerObserver;
use App\Observers\Exhibitions\ExhibitionObserver;
use App\Observers\Exhibitions\ExhibitionCategoryObserver;
use App\Observers\Exhibitions\PopupObserver;
use App\Observers\InquiryAnswerObserver;
use App\Observers\EmailTemplateObserver;
use App\Observers\PostObserver;
use App\Observers\TermsOfUseObserver;
use App\Observers\TooltipObserver;
use App\Observers\UserAdvAgreeObserver;
use App\Observers\UserObserver;
use App\Observers\UserSiteObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        DataCreated::class => [
            RemainActionLog::class
        ],
        DataUpdated::class => [
            RemainActionLog::class
        ],
        DataDeleted::class => [
            RemainActionLog::class
        ],
        Login::class => [
            RemainActionLog::class
        ],
        Logout::class => [
            RemainActionLog::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Banner::observe(ExhibitionObserver::class);
        BannerDeviceContent::observe(ExhibitionObserver::class);
        Board::observe(BoardObserver::class);
        EmailTemplate::observe(EmailTemplateObserver::class);
        Exhibition::observe(ExhibitionObserver::class);
        ExhibitionTargetUser::observe(ExhibitionObserver::class);
        ExhibitionCategory::observe(ExhibitionCategoryObserver::class);
        InquiryAnswer::observe(InquiryAnswerObserver::class);
        Popup::observe(ExhibitionObserver::class);
        PopupDeviceContent::observe(ExhibitionObserver::class);
        Post::observe(PostObserver::class);
        TermsOfUse::observe(TermsOfUseObserver::class);
        Tooltip::observe(TooltipObserver::class);
        User::observe(UserObserver::class);
        UserAdvAgree::observe(UserAdvAgreeObserver::class);
        UserSite::observe(UserSiteObserver::class);
    }

    protected $subscribe = [
        'App\Listeners\MemberEventSubscriber'
    ];
}
