<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
//            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
//            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'attach' => 'App\Models\Attach\AttachFile',
            'banner' => 'App\Models\Exhibitions\Banner',
            'banner_content' => 'App\Models\Exhibitions\BannerDeviceContent',
            'board' => 'App\Models\Board',
            'email_template' => 'App\Models\EmailTemplate',
            'exception' => 'App\Models\Exception',
            'exhibition' => 'App\Models\Exhibitions\Exhibition',
            'exhibition_category' => 'App\Models\Exhibitions\ExhibitionCategory',
            'exhibition_target_user' => 'App\Models\Exhibitions\ExhibitionTargetUser',
            'inquiry' => 'App\Models\Inquiry',
            'popup' => 'App\Models\Exhibitions\Popup',
            'popup_content' => 'App\Models\Exhibitions\PopupDeviceContent',
            'post' => 'App\Models\Post',
            'post_thumbnail' => 'App\Models\PostThumbnail',
            'terms_of_use' => 'App\Models\TermsOfUse',
            'tooltip' => 'App\Models\Tooltip',
            'user' => 'App\Models\Users\User',
            'user_site' => 'App\Models\Users\UserSite',
        ]);
    }
}
