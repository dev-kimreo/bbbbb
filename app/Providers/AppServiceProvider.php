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
        /*
        if ($this->app->isLocal()) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
        */
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap(
            [
                'attach' => 'App\Models\Attach\AttachFile',
                'attach_thumbnail' => 'App\Models\Attach\AttachThumb',
                'banner' => 'App\Models\Exhibitions\Banner',
                'banner_content' => 'App\Models\Exhibitions\BannerDeviceContent',
                'board' => 'App\Models\Boards\Board',
                'component' => 'App\Models\Components\Component',
                'component_upload_image' => 'App\Models\Attach\ComponentUploadImage',
                'email_template' => 'App\Models\EmailTemplate',
                'exception' => 'App\Models\Exception',
                'word' => 'App\Models\Word',
                'exhibition' => 'App\Models\Exhibitions\Exhibition',
                'exhibition_category' => 'App\Models\Exhibitions\ExhibitionCategory',
                'exhibition_target_user' => 'App\Models\Exhibitions\ExhibitionTargetUser',
                'inquiry' => 'App\Models\Inquiries\Inquiry',
                'linked_component' => 'App\Models\LinkedComponents\LinkedComponent',
                'popup' => 'App\Models\Exhibitions\Popup',
                'popup_content' => 'App\Models\Exhibitions\PopupDeviceContent',
                'post' => 'App\Models\Boards\Post',
                'terms_of_use' => 'App\Models\TermsOfUse',
                'tooltip' => 'App\Models\Tooltip',
                'user' => 'App\Models\Users\User',
                'user_site' => 'App\Models\Users\UserSite',
                'user_solution' => 'App\Models\Users\UserSolution',
            ]
        );
    }
}
