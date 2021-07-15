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
            'attach' => 'App\Models\AttachFile',
            'banner' => 'App\Models\Exhibitions\Banner',
            'banner_content' => 'App\Models\Exhibitions\BannerDeviceContent',
            'board' => 'App\Models\Board',
            'exception' => 'App\Models\Exception',
            'inquiry' => 'App\Models\Inquiry',
            'popup' => 'App\Models\Exhibitions\Popup',
            'post' => 'App\Models\Post',
            'post_thumbnail' => 'App\Models\PostThumbnail',
            'tooltip' => 'App\Models\Tooltip',
            'terms_of_use' => 'App\Models\TermsOfUse',
            'user' => 'App\Models\User',
            'user_site' => 'App\Models\UserSite',
            'email_template' => 'App\Models\EmailTemplate',
            'exhibition_category' => 'App\Models\Exhibitions\ExhibitionCategory',
        ]);
    }
}
