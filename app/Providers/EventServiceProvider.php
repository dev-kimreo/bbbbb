<?php

namespace App\Providers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Listeners\RemainBackofficeLog;
use App\Models\Board;
use App\Models\InquiryAnswer;
use App\Models\EmailTemplate;
use App\Models\Post;
use App\Models\TermsOfUse;
use App\Models\Tooltip;
use App\Models\User;
use App\Models\UserAdvAgree;
use App\Observers\BoardObserver;
use App\Observers\InquiryAnswerObserver;
use App\Observers\EmailTemplateObserver;
use App\Observers\PostObserver;
use App\Observers\TermsOfUseObserver;
use App\Observers\TooltipObserver;
use App\Observers\UserAdvAgreeObserver;
use App\Observers\UserObserver;
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
            RemainBackofficeLog::class
        ],
        DataUpdated::class => [
            RemainBackofficeLog::class
        ],
        DataDeleted::class => [
            RemainBackofficeLog::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Board::observe(BoardObserver::class);
        Post::observe(PostObserver::class);
        Tooltip::observe(TooltipObserver::class);
        TermsOfUse::observe(TermsOfUseObserver::class);
        InquiryAnswer::observe(InquiryAnswerObserver::class);
        User::observe(UserObserver::class);
        UserAdvAgree::observe(UserAdvAgreeObserver::class);
        EmailTemplate::observe(EmailTemplateObserver::class);
    }

    protected $subscribe = [
        'App\Listeners\MemberEventSubscriber'
    ];
}
