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
            'board' => 'App\Models\Board',
            'inquiry' => 'App\Models\Inquiry',
            'post' => 'App\Models\Post',
            'post_thumbnail' => 'App\Models\PostThumbnail',
            'tooltip' => 'App\Models\Tooltip',
            'user' => 'App\Models\User',
        ]);
    }
}
